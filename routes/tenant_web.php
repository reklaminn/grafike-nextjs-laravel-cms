<?php

use App\Http\Controllers\Frontend\FormSubmissionController;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Frontend\LlmsController;
use App\Http\Controllers\Frontend\MemberAuthController;
use App\Http\Controllers\Frontend\MemberPasswordController;
use App\Http\Controllers\Frontend\PageUnlockController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\Frontend\RobotsController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\Frontend\WellKnownController;
use Illuminate\Support\Facades\Route;

// Sitemap
Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Dynamic robots.txt — respects admin AI-bot policy settings
Route::get('robots.txt', RobotsController::class)->name('robots');

// LLM discovery files (llmstxt.org spec)
Route::get('llms.txt',      [LlmsController::class, 'index'])->name('llms');
Route::get('llms-full.txt', [LlmsController::class, 'full'])->name('llms.full');

// AI discovery files (ai.txt + MCP manifest)
Route::get('ai.txt',                    [WellKnownController::class, 'aiTxt'])->name('ai.txt');
Route::get('.well-known/ai.txt',        [WellKnownController::class, 'aiTxt'])->name('well-known.ai-txt');
Route::get('.well-known/mcp.json',      [WellKnownController::class, 'mcpJson'])->name('well-known.mcp');

// IndexNow key file — served dynamically from admin settings
// The key must match SiteSetting: services.indexnow_key
Route::get('{key}.txt', function (string $key) {
    $storedKey = \App\Models\SiteSetting::get('services.indexnow_key', '');

    if (empty($storedKey) || $key !== $storedKey) {
        abort(404);
    }

    return response($storedKey, 200)
        ->header('Content-Type', 'text/plain');
})->where('key', '[a-zA-Z0-9]{8,128}')->name('indexnow.key');

// Language switch
Route::get('lang/{code}', function (string $code) {
    if (in_array($code, ['tr', 'en', 'de', 'ru', 'fr', 'ar'])) {
        session(['locale' => $code]);
    }

    return redirect()->back();
})->name('lang.switch');

// Form submission — spam koruması: IP başına dakikada 10 gönderim
Route::post('forms/{form}/submit', [FormSubmissionController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('forms.submit');

// Review submission
Route::post('reviews', [ReviewController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('reviews.store');

// Page unlock (password-protected pages) — brute-force koruması
Route::post('pages/{page}/unlock', [PageUnlockController::class, 'unlock'])
    ->middleware('throttle:10,1')
    ->name('pages.unlock');

// Member Authentication
Route::prefix('member')->name('member.')->group(function () {
    Route::get('login', [MemberAuthController::class, 'showLogin'])->name('login');
    // Brute-force koruması: IP başına dakikada 5 deneme
    Route::post('login', [MemberAuthController::class, 'login'])
        ->middleware('throttle:5,1')->name('login.submit');
    Route::get('register', [MemberAuthController::class, 'showRegister'])->name('register');
    Route::post('register', [MemberAuthController::class, 'register'])
        ->middleware('throttle:5,1')->name('register.submit');

    // Password reset
    Route::get('password/forgot',        [MemberPasswordController::class, 'showForgot'])->name('password.forgot');
    Route::post('password/email',        [MemberPasswordController::class, 'sendResetLink'])
        ->middleware('throttle:3,1')->name('password.email');
    Route::get('password/reset/{token}', [MemberPasswordController::class, 'showReset'])->name('password.reset');
    Route::post('password/reset',        [MemberPasswordController::class, 'reset'])
        ->middleware('throttle:5,1')->name('password.update');

    Route::middleware('member.auth')->group(function () {
        Route::get('profile', [MemberAuthController::class, 'profile'])->name('profile');
        Route::put('profile', [MemberAuthController::class, 'updateProfile'])->name('profile.update');
        Route::post('logout', [MemberAuthController::class, 'logout'])->name('logout');
    });
});

// Frontend catch-all routes (must be last!)
Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('{slug}', [FrontendController::class, 'show'])
    ->where('slug', '^(?!admin|member).*$')
    ->name('page.show');
