<?php

namespace App\Notifications;

use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Branded password-reset email for tenant members.
 *
 * Reads site name, logo and primary colour from the tenant's SiteSetting
 * table (stancl keeps the connection scoped to the active tenant).
 *
 * The SMTP credentials come from the global .env (MAIL_*) while the
 * display "From" name is overridden to the tenant site title so the
 * member sees "Nuh Çiçek Diş Kliniği <noreply@…>" in their inbox.
 */
class MemberResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $token) {}

    // ─────────────────────────────────────────────────────────────────────────

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        // ── Tenant branding ───────────────────────────────────────────────
        $siteName     = SiteSetting::get('site.title', config('cms.name', config('app.name')));
        $logoUrl      = SiteSetting::get('design.logo_url', '');
        $primaryColor = SiteSetting::get('design.primary_color', '#6366f1');
        $contactEmail = SiteSetting::get('contact.email', '');
        $siteUrl      = rtrim(url('/'), '/');

        // ── Reset URL ─────────────────────────────────────────────────────
        // Route 'password.reset' is the member password reset route
        // registered in tenant_web.php as:  /member/password/reset/{token}
        $resetUrl = url(route('password.reset', ['token' => $this->token], false))
                  . '?email=' . urlencode((string) $notifiable->getEmailForPasswordReset());

        $expireMinutes = config('auth.passwords.members.expire', 60);

        // ── From address ─────────────────────────────────────────────────
        // SMTP auth uses the env MAIL_FROM_ADDRESS; only the display name is
        // overridden to the tenant site title so the email looks branded.
        $fromAddress = config('mail.from.address', 'noreply@example.com');
        $fromName    = $siteName;

        return (new MailMessage)
            ->subject("Şifre Sıfırlama — {$siteName}")
            ->from($fromAddress, $fromName)
            ->replyTo($contactEmail ?: $fromAddress, $siteName)
            ->view('emails.member.reset-password', [
                'memberName'    => $notifiable->name,
                'resetUrl'      => $resetUrl,
                'siteName'      => $siteName,
                'siteUrl'       => $siteUrl,
                'logoUrl'       => $logoUrl,
                'primaryColor'  => $primaryColor,
                'expireMinutes' => $expireMinutes,
            ]);
    }
}
