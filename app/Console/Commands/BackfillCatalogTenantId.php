<?php

namespace App\Console\Commands;

use App\Models\SectionTemplate;
use App\Models\Tenant;
use App\Models\Theme;
use Illuminate\Console\Command;

/**
 * One-off (idempotent) backfill for the hybrid tenant-scoped catalog.
 *
 * Rule: a theme used by EXACTLY ONE tenant (via tenant->theme_id) becomes that
 * tenant's own (tenant_id = key); its section_templates inherit the same owner.
 * A theme used by zero or by multiple tenants stays GLOBAL (tenant_id = NULL,
 * shared library).
 *
 * Safe to re-run. Default is dry-run; pass --commit to write.
 *
 *   php artisan catalog:backfill-tenant-id            # preview
 *   php artisan catalog:backfill-tenant-id --commit   # apply
 */
class BackfillCatalogTenantId extends Command
{
    protected $signature = 'catalog:backfill-tenant-id {--commit : Apply changes (default is a dry-run preview)}';

    protected $description = 'Assign tenant_id to themes/section_templates from tenant theme usage (single-user → owned, shared/unused → global).';

    public function handle(): int
    {
        $commit = (bool) $this->option('commit');

        $this->info($commit
            ? '== APPLYING tenant_id backfill =='
            : '== DRY-RUN (no changes) — use --commit to apply ==');

        // Build: theme_id => [tenant keys that select it]
        $usage = [];
        foreach (Tenant::all() as $tenant) {
            $themeId = $tenant->theme_id;
            if ($themeId !== null && $themeId !== '') {
                $usage[(string) $themeId][] = $tenant->getTenantKey();
            }
        }

        $owned = 0;
        $global = 0;

        foreach (Theme::query()->get() as $theme) {
            $users = array_values(array_unique($usage[(string) $theme->id] ?? []));
            $blockCount = SectionTemplate::withTrashed()->where('theme_id', $theme->id)->count();

            if (count($users) === 1) {
                $owner = $users[0];
                $this->line("  #{$theme->id} \"{$theme->name}\"  ->  OWNED by '{$owner}'  (+{$blockCount} blok)");

                if ($commit) {
                    $theme->tenant_id = $owner;
                    $theme->save();
                    SectionTemplate::withTrashed()
                        ->where('theme_id', $theme->id)
                        ->update(['tenant_id' => $owner]);
                }

                $owned++;
            } else {
                $why = count($users) === 0 ? 'hiçbir tenant kullanmıyor' : 'birden çok tenant kullanıyor ('.count($users).')';
                $this->line("  #{$theme->id} \"{$theme->name}\"  ->  GLOBAL  ({$why}, +{$blockCount} blok)");

                if ($commit) {
                    $theme->tenant_id = null;
                    $theme->save();
                    SectionTemplate::withTrashed()
                        ->where('theme_id', $theme->id)
                        ->update(['tenant_id' => null]);
                }

                $global++;
            }
        }

        $this->newLine();
        $this->info("Bitti — owned={$owned}, global={$global}" . ($commit ? '  (uygulandı)' : '  (dry-run)'));

        return self::SUCCESS;
    }
}
