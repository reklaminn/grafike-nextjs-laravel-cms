<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('site', [SiteController::class, 'index']);
    Route::get('settings', [SettingsController::class, 'index']);
    Route::get('menus', [MenuController::class, 'index']);
    Route::get('menus/{location}', [MenuController::class, 'show']);
    Route::get('pages/{parent}/children', [PageController::class, 'children'])->whereNumber('parent');
    Route::get('pages/{slug}', [PageController::class, 'show']);
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{slug}', [ArticleController::class, 'show']);
    Route::get('search', SearchController::class)->middleware('throttle:30,1');
    Route::get('forms/{form}', [FormController::class, 'show']);
    // Spam/abuse koruması: IP başına dakikada 10 gönderim
    Route::post('forms/{form}/submit', [FormController::class, 'submit'])
        ->middleware('throttle:10,1');
});
