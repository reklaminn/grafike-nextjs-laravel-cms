<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DesignController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\LanguageController;
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
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\AiAssistantController;
use App\Http\Controllers\Admin\TranslationController;
use Illuminate\Support\Facades\Route;

// Admin Auth Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware('admin.auth')->group(function () {
        // ── Tenant Management (central DB — no tenant.admin middleware needed) ──
        Route::resource('tenants', TenantController::class)->except('edit');
        Route::post('tenants/{tenant}/provision', [TenantController::class, 'provision'])->name('tenants.provision');
        Route::post('tenants/{tenant}/switch',    [TenantController::class, 'switchTo'])->name('tenants.switch');
        Route::post('tenants/clear-active',       [TenantController::class, 'clearActive'])->name('tenants.clear-active');

        // ── Tenant-scoped routes (require active tenant in session) ───────────
        Route::middleware('tenant.admin')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Pages CRUD
        Route::resource('pages', PageController::class);
        Route::post('pages/reorder', [PageController::class, 'reorder'])->name('pages.reorder');
        Route::post('pages/{page}/migrate-to-sections', [PageController::class, 'migrateToSections'])->name('pages.migrate-to-sections');
        Route::get('pages/{page}/migrate-preview', [PageController::class, 'migratePreview'])->name('pages.migrate-preview');
        Route::post('pages/{page}/revisions/{revision}/restore', [PageController::class, 'restoreRevision'])->name('pages.restore-revision');
        Route::get('pages/{page}/create-translation', [PageController::class, 'createTranslation'])->name('pages.create-translation');

        // Articles CRUD
        Route::resource('articles', ArticleController::class);
        Route::delete('articles/{article}/cover', [ArticleController::class, 'destroyCover'])->name('articles.cover-destroy');
        Route::get('articles/{article}/create-translation', [ArticleController::class, 'createTranslation'])->name('articles.create-translation');

        // Menus CRUD
        Route::resource('menus', MenuController::class);
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

        // Languages Management
        Route::resource('languages', LanguageController::class)->except('show');
        Route::get('languages-translations', [LanguageController::class, 'translations'])->name('languages.translations');
        Route::post('languages-translations', [LanguageController::class, 'saveTranslation'])->name('languages.save-translation');
        Route::delete('languages-translations/{translation}', [LanguageController::class, 'deleteTranslation'])->name('languages.delete-translation');

        // Design (CSS/JS Editor)
        Route::get('design', [DesignController::class, 'index'])->name('design.index');
        Route::put('design', [DesignController::class, 'update'])->name('design.update');
        Route::resource('themes', ThemeController::class)->except('show');
        Route::get('section-templates/menu-placeholders', [SectionTemplateController::class, 'menuPlaceholders'])->name('section-templates.menu-placeholders');
        Route::match(['GET', 'POST'], 'section-templates/{section_template}/preview', [SectionTemplateController::class, 'preview'])->name('section-templates.preview');
        Route::post('section-templates/{section_template}/restore', [SectionTemplateController::class, 'restore'])->name('section-templates.restore')->withTrashed();
        Route::delete('section-templates/{section_template}/force-delete', [SectionTemplateController::class, 'forceDelete'])->name('section-templates.force-delete')->withTrashed();
        Route::get('section-templates/{section_template}/versions', [SectionTemplateController::class, 'versions'])->name('section-templates.versions');
        Route::post('section-templates/{section_template}/save-version', [SectionTemplateController::class, 'saveVersion'])->name('section-templates.save-version');
        Route::post('section-templates/{section_template}/versions/{version}/restore', [SectionTemplateController::class, 'restoreVersion'])->name('section-templates.restore-version');
        Route::resource('section-templates', SectionTemplateController::class)->except('show');
        Route::post('section-templates/{section_template}/duplicate', [SectionTemplateController::class, 'duplicate'])->name('section-templates.duplicate');

        // SMTP Profiles
        Route::resource('smtp-profiles', SmtpProfileController::class)->except('show');
        Route::post('smtp-profiles/{smtp_profile}/test', [SmtpProfileController::class, 'sendTest'])->name('smtp-profiles.test');

        // Currencies
        Route::resource('currencies', CurrencyController::class)->except('show');
        Route::post('currencies/fetch-rates', [CurrencyController::class, 'fetchRates'])->name('currencies.fetch-rates');

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

        // AI Assistant API
        Route::post('ai/translate', [AiAssistantController::class, 'translate'])->name('ai.translate');
        Route::post('ai/rewrite', [AiAssistantController::class, 'rewrite'])->name('ai.rewrite');
        Route::post('ai/generate-meta', [AiAssistantController::class, 'generateMeta'])->name('ai.generate-meta');
        Route::post('ai/translate-content', [AiAssistantController::class, 'translateContent'])->name('ai.translate-content');

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

        }); // end: tenant.admin middleware group
    });
});
