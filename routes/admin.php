<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\Ai\BlockEditController as AiBlockEditController;
use App\Http\Controllers\Admin\Ai\PageGenerateController as AiPageGenerateController;
use App\Http\Controllers\Admin\Ai\SectionTemplateGenerateController as AiSectionTemplateGenerateController;
use App\Http\Controllers\Admin\Ai\SeoMetaController as AiSeoMetaController;
use App\Http\Controllers\Admin\Ai\StreamBlockEditController as AiStreamBlockEditController;
use App\Http\Controllers\Admin\Ai\TranslateContentController as AiTranslateContentController;
use App\Http\Controllers\Admin\AiDashboardController;
use App\Http\Controllers\Admin\AiUsageController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DesignController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\IndustryTemplateController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\Library\LegacyImportController;
use App\Http\Controllers\Admin\Library\LibraryHubController;
use App\Http\Controllers\Admin\Library\LibraryPortController;
use App\Http\Controllers\Admin\Library\LibraryShipCompanyController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SectionTemplateController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SitemapController;
use App\Http\Controllers\Admin\SmtpProfileController;
use App\Http\Controllers\Admin\AiPlanController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\TenantBackupController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\TenantTeamController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\SiteWizardController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\MailboxController;
use Illuminate\Support\Facades\Route;

// Admin Auth Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    // Brute-force koruması: IP başına dakikada 5 deneme
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware('admin.auth')->group(function () {
        // ── Tenant Management (central DB — no tenant.admin middleware needed) ──
        Route::resource('tenants', TenantController::class)->except('edit');
        Route::post('tenants/{tenant}/provision', [TenantController::class, 'provision'])->name('tenants.provision');
        Route::post('tenants/{tenant}/switch',    [TenantController::class, 'switchTo'])->name('tenants.switch');
        Route::post('tenants/clear-active',       [TenantController::class, 'clearActive'])->name('tenants.clear-active');

        // ── Package + AI Plan Management ─────────────────────────────────────
        Route::resource('packages', PackageController::class)->except(['show']);
        Route::resource('ai-plans', AiPlanController::class)->except(['show']);

        // ── Tenant Backups ────────────────────────────────────────────────────
        Route::post  ('tenants/{tenant}/backups',                    [TenantBackupController::class, 'store'])   ->name('tenants.backups.store');
        Route::get   ('tenants/{tenant}/backups/{filename}/download',[TenantBackupController::class, 'download'])->name('tenants.backups.download');
        Route::post  ('tenants/{tenant}/backups/{filename}/restore', [TenantBackupController::class, 'restore']) ->name('tenants.backups.restore');
        Route::delete('tenants/{tenant}/backups/{filename}',         [TenantBackupController::class, 'destroy']) ->name('tenants.backups.destroy');

        // ── Tenant AI Settings (BYOK) ─────────────────────────────────────────
        Route::put ('tenants/{tenant}/ai-settings',      [TenantController::class, 'updateAiSettings'])->name('tenants.ai-settings.update');
        Route::post('tenants/{tenant}/ai-settings/test', [TenantController::class, 'testAiKey'])      ->name('tenants.ai-settings.test');

        // ── Tenant Vertical Modules (Tours, Commerce, …) ─────────────────────
        Route::put('tenants/{tenant}/modules', [TenantController::class, 'updateModules'])->name('tenants.modules.update');
        Route::put('tenants/{tenant}/mailcow-domain', [TenantController::class, 'updateMailcowDomain'])->name('tenants.mailcow-domain.update');
        Route::post('tenants/{tenant}/quota-extensions', [TenantController::class, 'storeQuotaExtension'])->name('tenants.quota-extensions.store');
        Route::delete('tenants/{tenant}/quota-extensions/{extension}', [TenantController::class, 'destroyQuotaExtension'])->name('tenants.quota-extensions.destroy');

        // ── Tenant Iyzico Settings (BYOK) ─────────────────────────────────────
        Route::put('tenants/{tenant}/iyzico-settings', [TenantController::class, 'updateIyzicoSettings'])->name('tenants.iyzico-settings.update');

        // ── Industry SiteTemplates (FAZ 4.3 gallery) ─────────────────────────
        Route::get ('industry-templates',                 [IndustryTemplateController::class, 'index'])->name('industry-templates.index');
        Route::post('tenants/{tenant}/apply-industry-template',
                                                          [IndustryTemplateController::class, 'apply'])->name('tenants.apply-industry-template');

        // ── AI Usage Dashboards (FAZ 3.7) ────────────────────────────────────
        Route::get ('tenants/{tenant}/ai-usage', [AiUsageController::class, 'show'])->name('tenants.ai-usage');
        Route::get ('ai-dashboard',              [AiDashboardController::class, 'index'])->name('ai-dashboard');

        // ── Tenant-scoped routes (require active tenant in session) ───────────
        Route::middleware('tenant.admin')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('search', \App\Http\Controllers\Admin\GlobalSearchController::class)->name('search');

        // Pages CRUD
        // catalog-json resource'tan ÖNCE — yoksa pages/{page} show ile çakışır
        Route::get('pages/catalog-json', [PageController::class, 'catalogJson'])->name('pages.catalog-json');
        Route::resource('pages', PageController::class);
        Route::post('pages/reorder', [PageController::class, 'reorder'])->name('pages.reorder');
        Route::post('pages/{page}/migrate-to-sections', [PageController::class, 'migrateToSections'])->name('pages.migrate-to-sections');
        Route::post('pages/{page}/ai-generate-blocks',  [PageController::class, 'aiGenerateBlocks'])->name('pages.ai-generate-blocks');
        Route::get('pages/{page}/migrate-preview', [PageController::class, 'migratePreview'])->name('pages.migrate-preview');
        Route::post('pages/{page}/revisions/{revision}/restore', [PageController::class, 'restoreRevision'])->name('pages.restore-revision');
        Route::get('pages/{page}/create-translation', [PageController::class, 'createTranslation'])->name('pages.create-translation');

        // AI helpers (FAZ 4.1+)
        Route::post('pages/{page}/ai/seo-meta',       AiSeoMetaController::class)->name('pages.ai.seo-meta');
        Route::post('ai/block-edit',                  AiBlockEditController::class)->name('ai.block-edit');
        Route::post('ai/pages/generate',              AiPageGenerateController::class)->name('ai.pages.generate');
        Route::get('ai/pages/generate/status/{jobId}', [AiPageGenerateController::class, 'status'])->name('ai.generate-page.status');
        Route::post('ai/section-templates/generate',  AiSectionTemplateGenerateController::class)->name('ai.section-templates.generate');

        // Prompt boost — ham tarifi ayrıntılı prompt'a dönüştürür (sayfa sihirbazı).
        Route::post('ai/boost-prompt',                \App\Http\Controllers\Admin\Ai\BoostPromptController::class)->name('ai.boost-prompt');

        // Streaming SSE endpoint (FAZ 3.6) — text deltas + final usage event.
        Route::post('ai/stream/block-edit',           AiStreamBlockEditController::class)->name('ai.stream.block-edit');

        // Articles CRUD
        Route::resource('articles', ArticleController::class)->except('show');
        Route::delete('articles/{article}/cover', [ArticleController::class, 'destroyCover'])->name('articles.cover-destroy');
        Route::get('articles/{article}/create-translation', [ArticleController::class, 'createTranslation'])->name('articles.create-translation');

        // Menus CRUD
        Route::resource('menus', MenuController::class)->except('show');
        Route::post('menus/{menu}/items', [MenuController::class, 'addItem'])->name('menus.add-item');
        Route::put('menus/{menu}/items/reorder', [MenuController::class, 'reorderItems'])->name('menus.reorder-items');
        Route::delete('menus/{menu}/items/{item}', [MenuController::class, 'deleteItem'])->name('menus.delete-item');

        // Forms CRUD
        Route::resource('forms', FormController::class);
        Route::get('forms/{form}/submissions', [FormController::class, 'submissions'])->name('forms.submissions');
        Route::post('forms/{form}/fields', [FormController::class, 'saveField'])->name('forms.save-field');
        Route::delete('forms/{form}/fields/{field}', [FormController::class, 'deleteField'])->name('forms.delete-field');
        Route::get('forms/{form}/export', [FormController::class, 'exportSubmissions'])->name('forms.export');

        // SEO Management
        Route::get('seo', [SeoController::class, 'index'])->name('seo.index');
        Route::get('seo/analysis', [SeoController::class, 'bulkAnalysis'])->name('seo.analysis');
        Route::get('seo/{seoEntry}/edit', [SeoController::class, 'edit'])->name('seo.edit');
        Route::put('seo/{seoEntry}', [SeoController::class, 'update'])->name('seo.update');
        Route::delete('seo/{seoEntry}', [SeoController::class, 'destroy'])->name('seo.destroy');
        // AJAX: Generate structured data / hreflang
        Route::post('seo/{seoEntry}/generate-structured-data', [SeoController::class, 'generateStructuredData'])->name('seo.generate-structured-data');
        Route::post('seo/{seoEntry}/generate-hreflang', [SeoController::class, 'generateHreflang'])->name('seo.generate-hreflang');

        // Redirects Management
        Route::resource('redirects', RedirectController::class)->except('show');
        Route::post('redirects/{redirect}/reset-hits', [RedirectController::class, 'resetHits'])->name('redirects.reset-hits');
        Route::get('redirects-import', [RedirectController::class, 'showImport'])->name('redirects.import');
        Route::post('redirects-import', [RedirectController::class, 'processImport'])->name('redirects.process-import');

        // Sitemap Configuration
        Route::get('sitemap', [SitemapController::class, 'index'])->name('sitemap.index');
        Route::put('sitemap', [SitemapController::class, 'update'])->name('sitemap.update');
        Route::post('sitemap/refresh', [SitemapController::class, 'refresh'])->name('sitemap.refresh');

        // Media Library
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media/upload', [MediaController::class, 'upload'])->name('media.upload');
        Route::get('media/{medium}', [MediaController::class, 'show'])->name('media.show');
        Route::put('media/{medium}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');
        Route::post('media/bulk-destroy', [MediaController::class, 'bulkDestroy'])->name('media.bulk-destroy');
        Route::post('media/generate-alt-bulk', [MediaController::class, 'generateAltBulk'])->name('media.generate-alt-bulk');
        Route::post('media/{medium}/generate-alt', [MediaController::class, 'generateAlt'])->name('media.generate-alt');

        // Reviews Moderation
        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::post('reviews/bulk-approve', [ReviewController::class, 'bulkApprove'])->name('reviews.bulk-approve');
        Route::post('reviews/bulk-destroy', [ReviewController::class, 'bulkDestroy'])->name('reviews.bulk-destroy');

        // Members Management
        Route::resource('members', MemberController::class)->except('show');
        Route::post('members/{member}/toggle-active', [MemberController::class, 'toggleActive'])->name('members.toggle-active');

        // Tenant Team — site owner manages own site's managers/editors (agency.admin DEĞİL;
        // controller içinde "aktif tenant owner" kontrolü var)
        Route::get   ('team',          [TenantTeamController::class, 'index'])  ->name('team.index');
        Route::post  ('team',          [TenantTeamController::class, 'store'])  ->name('team.store');
        Route::put   ('team/{admin}',  [TenantTeamController::class, 'update']) ->name('team.update');
        Route::delete('team/{admin}',  [TenantTeamController::class, 'destroy'])->name('team.destroy');

        // Languages Management
        Route::resource('languages', LanguageController::class)->except('show');
        Route::get('languages-translations', [LanguageController::class, 'translations'])->name('languages.translations');
        Route::post('languages-translations', [LanguageController::class, 'saveTranslation'])->name('languages.save-translation');
        Route::delete('languages-translations/{translation}', [LanguageController::class, 'deleteTranslation'])->name('languages.delete-translation');

        // Design (CSS/JS Editor) — per-tenant assets (design_assets table lives
        // in the tenant DB), so an active site is mandatory; otherwise the page
        // would read/write the central DB and surface unrelated data.
        Route::middleware('tenant.required')->group(function () {
            Route::get('design', [DesignController::class, 'index'])->name('design.index');
            Route::put('design', [DesignController::class, 'update'])->name('design.update');
        });
        Route::resource('themes', ThemeController::class)->except('show');
        Route::get('section-templates/menu-placeholders', [SectionTemplateController::class, 'menuPlaceholders'])->name('section-templates.menu-placeholders');
        Route::get('section-templates/catalog-json', [SectionTemplateController::class, 'catalogJson'])->name('section-templates.catalog-json');
        Route::match(['GET', 'POST'], 'section-templates/{section_template}/preview', [SectionTemplateController::class, 'preview'])->name('section-templates.preview');
        Route::post('section-templates/{section_template}/restore', [SectionTemplateController::class, 'restore'])->name('section-templates.restore')->withTrashed();
        Route::delete('section-templates/{section_template}/force-delete', [SectionTemplateController::class, 'forceDelete'])->name('section-templates.force-delete')->withTrashed();
        Route::get('section-templates/{section_template}/versions', [SectionTemplateController::class, 'versions'])->name('section-templates.versions');
        Route::post('section-templates/{section_template}/save-version', [SectionTemplateController::class, 'saveVersion'])->name('section-templates.save-version');
        Route::post('section-templates/{section_template}/versions/{version}/restore', [SectionTemplateController::class, 'restoreVersion'])->name('section-templates.restore-version');
        Route::resource('section-templates', SectionTemplateController::class)->except('show');
        Route::post('section-templates/{section_template}/duplicate', [SectionTemplateController::class, 'duplicate'])->name('section-templates.duplicate');

        // SMTP Profiles — per-tenant (smtp_profiles table lives in the tenant
        // DB and stores credentials); require an active site so profiles are
        // never read from / written to the central DB across tenants.
        Route::middleware('tenant.required')->group(function () {
            Route::resource('smtp-profiles', SmtpProfileController::class)->except('show');
            Route::post('smtp-profiles/{smtp_profile}/test', [SmtpProfileController::class, 'sendTest'])->name('smtp-profiles.test');
        });

        // Currencies
        Route::resource('currencies', CurrencyController::class)->except('show');
        Route::post('currencies/fetch-rates', [CurrencyController::class, 'fetchRates'])->name('currencies.fetch-rates');

        Route::middleware('agency.admin')->group(function () {
            // Admin Users
            Route::resource('admin-users', AdminUserController::class)->except('show');
            Route::post('admin-users/{admin_user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin-users.toggle-status');

            // Roles & Permissions
            Route::resource('roles', RoleController::class)->except('show');

            // Maintenance / DB Cleanup
            Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
            Route::post('maintenance/cleanup', [MaintenanceController::class, 'cleanup'])->name('maintenance.cleanup');

            // Activity Log
            Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

            // ── Cruise Kütüphanesi (global katalog — Phase 1.5.h, central DB) ──
            // Super-admin global gemi/liman/destinasyon master'larını yönetir;
            // tenant'lar bu kütüphaneyi değiştiremez, sadece import eder.
            Route::get('library', [LibraryHubController::class, 'index'])->name('library.index');
            // Upload-tabanlı legacy import (CSV/XLSX → kolon eşle → library_*)
            Route::get ('library/import',         [LegacyImportController::class, 'form'])   ->name('library.import.form');
            Route::post('library/import/preview', [LegacyImportController::class, 'preview'])->name('library.import.preview');
            Route::post('library/import/run',     [LegacyImportController::class, 'run'])    ->name('library.import.run');
            Route::resource('library/ship-companies', LibraryShipCompanyController::class)
                ->except('show')
                ->parameters(['ship-companies' => 'shipCompany'])
                ->names('library.ship-companies');
            Route::resource('library/ports', LibraryPortController::class)
                ->except('show')
                ->names('library.ports');
        });

        // AI translate full page/article — wired into AiModelRouter (FAZ 4.6).
        // Legacy translate / rewrite / generate-meta endpoints were removed;
        // their replacements live under admin.ai.* (block-edit, pages.generate)
        // and admin.pages.ai.* (seo-meta).
        Route::post('ai/translate-content', AiTranslateContentController::class)->name('ai.translate-content');

        // Translation management
        Route::get('translations', [TranslationController::class, 'index'])->name('translations.index');
        Route::post('translations/bulk', [TranslationController::class, 'bulk'])->name('translations.bulk');

        // Settings
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

        // Business / LocalBusiness structured data settings
        Route::get('settings/business', [SettingsController::class, 'business'])->name('settings.business');
        Route::put('settings/business', [SettingsController::class, 'updateBusiness'])->name('settings.business.update');

        // Crawl / robots / llms settings
        Route::get('settings/crawl', [SettingsController::class, 'crawl'])->name('settings.crawl');
        Route::put('settings/crawl', [SettingsController::class, 'updateCrawl'])->name('settings.crawl.update');

        // Media upload / compression defaults
        Route::get('settings/media', [SettingsController::class, 'media'])->name('settings.media');
        Route::put('settings/media', [SettingsController::class, 'updateMedia'])->name('settings.media.update');

        // Sistem geneli AI Anahtarları — sadece agency admin (superadmin) erişebilir
        Route::get('settings/ai-keys',  [SystemSettingsController::class, 'aiKeys'])->name('settings.ai-keys');
        Route::post('settings/ai-keys', [SystemSettingsController::class, 'updateAiKeys'])->name('settings.ai-keys.update');

        // Mail (Mailcow) ayarları — agency admin
        Route::get ('settings/mailcow',       [SystemSettingsController::class, 'mailcow'])->name('settings.mailcow');
        Route::post('settings/mailcow',       [SystemSettingsController::class, 'updateMailcow'])->name('settings.mailcow.update');
        Route::post('settings/mailcow/test',  [SystemSettingsController::class, 'testMailcow'])->name('settings.mailcow.test');

        // ── Mail Hesapları (Mailcow entegrasyonu) ─────────────────────────────
        Route::prefix('mail')->name('mail.')->group(function () {
            Route::get('/', [MailboxController::class, 'index'])->name('index');

            // Mailbox CRUD
            Route::post('mailboxes',          [MailboxController::class, 'storeMailbox'])->name('mailboxes.store');
            Route::post('mailboxes/update',   [MailboxController::class, 'updateMailbox'])->name('mailboxes.update');
            Route::post('mailboxes/destroy',  [MailboxController::class, 'destroyMailbox'])->name('mailboxes.destroy');
            Route::post('mailboxes/password', [MailboxController::class, 'resetPassword'])->name('mailboxes.password');

            // Alias CRUD
            Route::post('aliases',         [MailboxController::class, 'storeAlias'])->name('aliases.store');
            Route::post('aliases/destroy', [MailboxController::class, 'destroyAlias'])->name('aliases.destroy');
        });

        // Mail template test
        Route::post('settings/test-mail', [SettingsController::class, 'sendTestMail'])->name('settings.test-mail');

        // ── Site Kurulum Sihirbazı ────────────────────────────────────────
        Route::prefix('site-wizard')->name('wizard.')->group(function () {
            Route::get('/',               [SiteWizardController::class, 'index'])->name('index');
            Route::post('/save-company',  [SiteWizardController::class, 'saveCompany'])->name('save-company');
            Route::post('/suggest-pages', [SiteWizardController::class, 'suggestPages'])->name('suggest-pages');
            Route::post('/generate-page',    [SiteWizardController::class, 'generatePage'])->name('generate-page');
            Route::post('/generate-article', [SiteWizardController::class, 'generateArticle'])->name('generate-article');
            Route::post('/complete',      [SiteWizardController::class, 'complete'])->name('complete');
            Route::post('/reset',         [SiteWizardController::class, 'reset'])->name('reset');
        });

        }); // end: tenant.admin middleware group
    });
});
