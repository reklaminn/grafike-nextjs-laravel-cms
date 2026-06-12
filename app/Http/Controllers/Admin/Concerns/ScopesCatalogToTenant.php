<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Shared tenant-scoping helpers for the central theme/block catalog
 * (themes, section_templates).  Used by Theme/SectionTemplate/Design
 * controllers.
 *
 * Visibility model (hybrid):
 *   - tenant_id = NULL → global/agency row: every tenant may USE it; only
 *                        agency (super) admins may EDIT it.
 *   - tenant_id = <key> → that tenant's own row: only that tenant (and agency
 *                        admins) may see/edit it.
 *
 * The "active tenant" is the site selected in the admin session
 * (`active_tenant`), the same key written by TenantController::switchTo and
 * read by InitializeTenancyForAdmin.
 */
trait ScopesCatalogToTenant
{
    /** Active tenant key for scoping, or null in agency/global context. */
    protected function catalogTenantId(): ?string
    {
        $id = session('active_tenant');

        return is_string($id) && $id !== '' ? $id : null;
    }

    /** Whether the current admin may manage GLOBAL (shared) catalog rows. */
    protected function canManageGlobalCatalog(): bool
    {
        return Auth::guard('admin')->user()?->isAgencyAdmin() ?? false;
    }

    /**
     * Vertical modules enabled for the active tenant, used to gate
     * module-specific GLOBAL catalog rows (e.g. Turizm theme = 'tours').
     * Returns null when no site is active (agency view → no gating, sees all).
     *
     * @return array<int, string>|null
     */
    protected function catalogModuleFilter(): ?array
    {
        $id = $this->catalogTenantId();
        if ($id === null) {
            return null;
        }

        $tenant = \App\Models\Tenant::find($id);

        return $tenant ? $tenant->enabledModules() : [];
    }

    /**
     * tenant_id to stamp on a newly created row.  Tenant admins always own
     * their rows; an agency admin with no active site creates a GLOBAL row,
     * and with an active site creates it for that site.
     */
    protected function newCatalogOwnerId(): ?string
    {
        return $this->catalogTenantId();
    }

    /**
     * Guard READ access (view/edit form/preview): agency admins see anything;
     * tenant admins may see global rows and their own.
     */
    protected function authorizeCatalogRead(?string $rowTenantId): void
    {
        if ($this->canManageGlobalCatalog()) {
            return;
        }

        abort_unless(
            $rowTenantId === null || $rowTenantId === $this->catalogTenantId(),
            403,
            'Bu kayıt başka bir siteye ait.'
        );
    }

    /**
     * Guard WRITE access (update/delete/version): agency admins may write
     * anything; tenant admins may write ONLY their own rows (never global).
     */
    protected function authorizeCatalogWrite(?string $rowTenantId): void
    {
        if ($this->canManageGlobalCatalog()) {
            return;
        }

        abort_unless(
            $rowTenantId !== null && $rowTenantId === $this->catalogTenantId(),
            403,
            'Bu kayıt düzenlenemez: küresel veya başka bir siteye ait.'
        );
    }
}
