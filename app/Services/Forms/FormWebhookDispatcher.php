<?php

namespace App\Services\Forms;

use App\Models\Form;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * POSTs a form submission to the form's configured outbound webhook
 * (e.g. a SendPulse "raw POST" automation URL), when enabled.
 *
 * Payload = the submitted field values (name => value) at the top level
 * (so receivers like SendPulse map `email` / `phone` / `name` straight to
 * contact fields), plus a few `_`-prefixed meta keys.
 *
 * Best-effort: failures are logged, never thrown — a webhook outage must not
 * break the visitor's form submission. Intended to be dispatched
 * ->afterResponse() so it adds zero latency to the response.
 */
class FormWebhookDispatcher
{
    /**
     * @param  array<string, mixed>  $fieldValues  name => value
     */
    public function dispatch(Form $form, array $fieldValues, ?string $ip = null): void
    {
        if (! $form->webhook_enabled || empty($form->webhook_url)) {
            return;
        }

        $payload = array_merge($fieldValues, [
            '_form'         => $form->name,
            '_form_id'      => $form->id,
            '_submitted_at' => now()->toIso8601String(),
            '_ip'           => $ip,
        ]);

        try {
            Http::asJson()
                ->timeout(8)
                ->post($form->webhook_url, $payload);
        } catch (\Throwable $e) {
            Log::warning("Form webhook failed [form {$form->id}]: {$e->getMessage()}");
        }
    }
}
