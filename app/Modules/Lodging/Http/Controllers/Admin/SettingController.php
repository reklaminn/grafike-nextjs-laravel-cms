<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Lodging\Models\LodgingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Konaklama modülü ayarları — bildirim e-postası, WhatsApp numarası,
 * rezervasyon kod ön eki.
 */
class SettingController extends Controller
{
    public function edit(): View
    {
        return view('lodging::admin.settings.edit', [
            'setting' => LodgingSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notification_email'      => ['nullable', 'email', 'max:255'],
            'whatsapp_number'         => ['nullable', 'string', 'max:32'],
            'reservation_code_prefix' => ['nullable', 'string', 'max:8'],
            'default_currency'        => ['nullable', 'string', 'size:3'],
        ]);

        $setting = LodgingSetting::current();
        $setting->update([
            'notification_email'      => $data['notification_email'] ?? null,
            'whatsapp_number'         => preg_replace('/\s+/', '', (string) ($data['whatsapp_number'] ?? '')) ?: null,
            'reservation_code_prefix' => strtoupper($data['reservation_code_prefix'] ?? 'RZ'),
            'default_currency'        => strtoupper($data['default_currency'] ?? 'TRY'),
        ]);

        return back()->with('success', 'Ayarlar kaydedildi.');
    }
}
