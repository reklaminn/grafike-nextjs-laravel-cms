<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class FormController extends Controller
{
    /**
     * GET /api/v1/forms/{form}
     * Returns form definition (fields) so Next.js can render the form.
     */
    public function show(Form $form): JsonResponse
    {
        if (! $form->is_active) {
            return response()->json(['error' => 'Form aktif değil.'], 404);
        }

        $form->load('fields');

        return response()->json([
            'data' => [
                'id'                => $form->id,
                'name'              => $form->name,
                'slug'              => $form->slug,
                'description'       => $form->description,
                'requires_captcha'  => $form->requires_captcha,
                // Public site key — only sent when Turnstile is enabled AND the
                // form requires a captcha, so the frontend renders the widget.
                'turnstile_site_key' => ($form->requires_captcha && config('cms.turnstile.enabled'))
                    ? config('cms.turnstile.site_key')
                    : null,
                'fields'            => $form->fields->map(fn ($f) => [
                    'id'            => $f->id,
                    'name'          => $f->name,
                    'label'         => $f->label,
                    'type'          => $f->type,
                    'placeholder'   => $f->placeholder,
                    'default_value' => $f->default_value,
                    'options'       => $f->options ?? [],
                    'is_required'   => $f->is_required,
                    'css_class'     => $f->css_class,
                    'section'       => $f->section,
                ])->values(),
            ],
        ]);
    }

    /**
     * POST /api/v1/forms/{form}/submit
     * JSON-based form submission (for Next.js fetch).
     */
    public function submit(Request $request, Form $form): JsonResponse
    {
        // Rate limit: 5 submissions per minute per IP
        $key = 'api-form-submit:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['error' => 'Çok fazla form gönderimi. Lütfen biraz bekleyin.'], 429);
        }
        RateLimiter::hit($key, 60);

        if (! $form->is_active || ! $form->allow_submissions) {
            return response()->json(['error' => 'Bu form şu anda aktif değil.'], 422);
        }

        // Honeypot: a hidden field humans never fill. If a bot filled it, fake
        // success (don't persist/email) so the bot wastes its attempt silently.
        if (filled($request->input('_hp_url'))) {
            return response()->json([
                'success' => true,
                'message' => 'Formunuz başarıyla gönderildi. Teşekkürler!',
            ]);
        }

        // Cloudflare Turnstile — verify when the form requires a captcha and
        // Turnstile is configured.
        if ($form->requires_captcha && config('cms.turnstile.enabled')) {
            if (! $this->verifyTurnstile($request->input('cf-turnstile-response'), $request->ip())) {
                return response()->json(['error' => 'Doğrulama başarısız. Lütfen tekrar deneyin.'], 422);
            }
        }

        // Build validation rules from form fields
        $rules = [];
        $fieldLabels = [];
        foreach ($form->fields as $field) {
            $fieldRules = [$field->is_required ? 'required' : 'nullable'];

            match ($field->type) {
                'email'  => $fieldRules[] = 'email',
                'number' => $fieldRules[] = 'numeric',
                'url'    => $fieldRules[] = 'url',
                'tel'    => $fieldRules[] = 'string|max:20',
                default  => $fieldRules[] = 'string|max:5000',
            };

            if ($field->validation_rules) {
                $fieldRules[] = $field->validation_rules;
            }

            $rules["fields.{$field->name}"]  = implode('|', $fieldRules);
            $fieldLabels["fields.{$field->name}"] = $field->label;
        }

        $validated = $request->validate($rules, [], $fieldLabels);

        // Build structured submission data
        $submissionData = [];
        foreach ($form->fields as $field) {
            $submissionData[$field->name] = [
                'label' => $field->label,
                'value' => $validated['fields'][$field->name] ?? null,
                'type'  => $field->type,
            ];
        }

        // Persist
        $submission = null;
        if ($form->save_to_database) {
            $submission = FormSubmission::create([
                'form_id'         => $form->id,
                'subject'         => $request->input('fields.subject', $form->name . ' Formu'),
                'data'            => $submissionData,
                'status'          => 'new',
                'ip_address'      => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'recipient_email' => $form->notification_email,
            ]);
        }

        // Email notification
        if ($form->notification_email) {
            try {
                $this->sendNotification($form, $submissionData, $submission);
            } catch (\Throwable $e) {
                Log::error("API form notification failed [{$form->id}]: {$e->getMessage()}");
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Formunuz başarıyla gönderildi. Teşekkürler!',
            'reference_id' => $submission?->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * Verify a Cloudflare Turnstile token server-side.
     * https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
     */
    protected function verifyTurnstile(?string $token, ?string $ip): bool
    {
        if (! $token) {
            return false;
        }

        try {
            $resp = Http::asForm()->timeout(8)->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'secret'   => (string) config('cms.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ],
            );

            return (bool) ($resp->json('success') ?? false);
        } catch (\Throwable $e) {
            Log::error('Turnstile verify failed: ' . $e->getMessage());

            return false;
        }
    }

    protected function sendNotification(Form $form, array $data, ?FormSubmission $submission): void
    {
        $subject = "Yeni Form Gönderimi: {$form->name}";
        $body    = "Form: {$form->name}\nTarih: " . now()->format('d.m.Y H:i') . "\n\n";

        foreach ($data as $field) {
            $body .= "{$field['label']}: {$field['value']}\n";
        }

        if ($submission) {
            $body .= "\nReferans No: #{$submission->id}";
        }

        \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($form, $subject) {
            $message->to($form->notification_email)->subject($subject);
        });
    }
}
