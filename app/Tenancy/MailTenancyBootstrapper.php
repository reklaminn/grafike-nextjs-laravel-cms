<?php

namespace App\Tenancy;

use App\Models\SmtpProfile;
use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;

/**
 * Makes the active tenant's default SMTP profile the runtime mailer, so EVERY
 * send while that tenant is active (contact/reservation form notifications,
 * Tours booking mails, member password resets, …) goes out through the
 * tenant's own SMTP account and From address — not a single shared global one.
 *
 * Runs on every TenancyInitialized (web request + queued jobs, because
 * QueueTenancyBootstrapper re-bootstraps in the worker) and reverts on
 * TenancyEnded so central-context sends keep the global config.
 *
 * Must be registered AFTER DatabaseTenancyBootstrapper in config/tenancy.php
 * so the tenant DB connection is active when we read smtp_profiles.
 *
 * Fallback chain: tenant default SmtpProfile → (none) → global config/mail.php
 * (.env MAIL_*). If a tenant has no profile, nothing is overridden.
 */
class MailTenancyBootstrapper implements TenancyBootstrapper
{
    /** Snapshot of the global mail config, restored on revert(). */
    protected ?array $originalMailConfig = null;

    public function __construct(protected Application $app)
    {
    }

    public function bootstrap(Tenant $tenant): void
    {
        try {
            $profile = SmtpProfile::query()->where('is_default', true)->first()
                ?? SmtpProfile::query()->orderBy('id')->first();
        } catch (\Throwable) {
            // smtp_profiles table missing (un-migrated tenant) or DB hiccup —
            // leave the global mailer in place rather than break tenancy init.
            return;
        }

        if (! $profile) {
            return;
        }

        $config = $this->app['config'];
        $this->originalMailConfig = $config->get('mail');

        $config->set('mail.default', 'smtp');
        $config->set('mail.mailers.smtp.transport', 'smtp');
        // Laravel 11/12 infers TLS from the scheme: smtps = implicit TLS (465),
        // smtp = STARTTLS auto-negotiated (587).
        $config->set('mail.mailers.smtp.scheme', $profile->encryption === 'ssl' ? 'smtps' : 'smtp');
        $config->set('mail.mailers.smtp.url', null);
        $config->set('mail.mailers.smtp.host', $profile->host);
        $config->set('mail.mailers.smtp.port', (int) $profile->port);
        $config->set('mail.mailers.smtp.username', $profile->username);
        $config->set('mail.mailers.smtp.password', $profile->password);
        $config->set('mail.from.address', $profile->from_email);
        $config->set('mail.from.name', $profile->from_name);

        // Drop any cached smtp mailer so the next send is built with the
        // tenant's config.
        $this->app['mail.manager']->purge('smtp');
    }

    public function revert(): void
    {
        if ($this->originalMailConfig === null) {
            return;
        }

        $this->app['config']->set('mail', $this->originalMailConfig);
        $this->app['mail.manager']->purge('smtp');
        $this->originalMailConfig = null;
    }
}
