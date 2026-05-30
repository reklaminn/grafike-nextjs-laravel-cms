<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class FormSubmissionController extends Controller
{
    public function store(Request $request, Form $form)
    {
        // Check rate limit (5 submissions per minute per IP)
        $key = 'form-submit:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->with('error', 'Çok fazla form gönderimi yapıldı. Lütfen biraz bekleyin.');
        }
        RateLimiter::hit($key, 60);

        // Check form is active
        if (! $form->is_active || ! $form->allow_submissions) {
            return back()->with('error', 'Bu form şu anda aktif değil.');
        }

        // Verify reCAPTCHA if required
        if ($form->requires_captcha && config('cms.recaptcha.enabled')) {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            if (! $recaptchaResponse || ! $this->verifyRecaptcha($recaptchaResponse)) {
                return back()->with('error', 'reCAPTCHA doğrulaması başarısız.')->withInput();
            }
        }

        // Build validation rules from form fields
        $rules = [];
        $fieldLabels = [];
        foreach ($form->fields as $field) {
            $fieldRules = [];
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Type-based validation
            match ($field->type) {
                'email' => $fieldRules[] = 'email',
                'number' => $fieldRules[] = 'numeric',
                'url' => $fieldRules[] = 'url',
                'tel' => $fieldRules[] = 'string|max:20',
                'file' => array_push($fieldRules, 'file', 'max:10240'),
                default => $fieldRules[] = 'string|max:5000',
            };

            // Custom validation rules from field config
            if ($field->validation_rules) {
                $fieldRules[] = $field->validation_rules;
            }

            $rules["fields.{$field->name}"] = implode('|', $fieldRules);
            $fieldLabels["fields.{$field->name}"] = $field->label;
        }

        $validated = $request->validate($rules, [], $fieldLabels);

        // Prepare submission data
        $submissionData = [];
        foreach ($form->fields as $field) {
            $value = $validated['fields'][$field->name] ?? null;
            $submissionData[$field->name] = [
                'label' => $field->label,
                'value' => $value,
                'type' => $field->type,
            ];
        }

        // Save to database
        $submission = null;
        if ($form->save_to_database) {
            $submission = FormSubmission::create([
                'form_id' => $form->id,
                'subject' => $request->input('fields.subject', $form->name . ' Formu'),
                'data' => $submissionData,
                'status' => 'new',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'recipient_email' => $form->notification_email,
            ]);
        }

        // Send notification email
        if ($form->notification_email) {
            try {
                $this->sendNotification($form, $submissionData, $submission);
            } catch (\Throwable $e) {
                Log::error("Form notification failed [{$form->id}]: {$e->getMessage()}");
            }
        }

        // Outbound webhook (e.g. SendPulse) — after response, no added latency.
        if ($form->webhook_enabled && $form->webhook_url) {
            $flatValues = array_map(fn ($f) => $f['value'] ?? null, $submissionData);
            $ip = $request->ip();
            app()->terminating(function () use ($form, $flatValues, $ip) {
                app(\App\Services\Forms\FormWebhookDispatcher::class)->dispatch($form, $flatValues, $ip);
            });
        }

        return back()->with('success', 'Formunuz başarıyla gönderildi. Teşekkürler!');
    }

    protected function verifyRecaptcha(string $response): bool
    {
        try {
            $result = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('cms.recaptcha.secret_key'),
                'response' => $response,
            ]);

            return $result->json('success', false);
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification failed: ' . $e->getMessage());

            return false;
        }
    }

    protected function sendNotification(Form $form, array $data, ?FormSubmission $submission): void
    {
        $subject = "Yeni Form Gönderimi: {$form->name}";
        $body = "Form: {$form->name}\n";
        $body .= "Tarih: " . now()->format('d.m.Y H:i') . "\n\n";

        foreach ($data as $fieldName => $field) {
            $body .= "{$field['label']}: {$field['value']}\n";
        }

        if ($submission) {
            $body .= "\nReferans No: #{$submission->id}";
        }

        // Use form-specific SMTP if configured, else default
        $mailer = $form->smtp_host ? $this->buildFormMailer($form) : Mail::mailer();

        $mailer->raw($body, function ($message) use ($form, $subject) {
            $message->to($form->notification_email)
                ->subject($subject);
        });
    }

    protected function buildFormMailer(Form $form)
    {
        $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
            $form->smtp_host,
            $form->smtp_port ?? 587,
            $form->smtp_encryption === 'tls',
        );

        if ($form->smtp_username) {
            $transport->setUsername($form->smtp_username);
            $transport->setPassword($form->smtp_password ?? '');
        }

        $mailer = new \Symfony\Component\Mailer\Mailer($transport);

        return new \Illuminate\Mail\Mailer('form', app('view'), $mailer, app('events'));
    }
}
