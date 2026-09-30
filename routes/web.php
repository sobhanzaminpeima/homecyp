<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SponsorClickController;
use App\Http\Controllers\LeadPasswordController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// Web installer (cPanel without terminal) — self-locks after first run
Route::get('/install', [InstallerController::class, 'index'])->name('installer.index');
Route::post('/install/run', [InstallerController::class, 'run'])->name('installer.run');
Route::get('/install/done', [InstallerController::class, 'done'])->name('installer.done');

// SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/healthz', function () {
    try {
        DB::select('select 1');
        Cache::put('healthz', now()->timestamp, 10);
        return response()->json(['status' => 'ok', 'database' => 'ok', 'cache' => Cache::get('healthz') ? 'ok' : 'degraded']);
    } catch (\Throwable $e) {
        report($e);
        return response()->json(['status' => 'degraded'], 503);
    }
})->middleware('throttle:30,1')->name('health');

// Language switcher
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// Sponsored campaign click tracking (spec section 9)
Route::get('/sponsor-click/{campaign}', SponsorClickController::class)->name('sponsor.click');

// Main pages
// Root is the full-screen AI chat experience (spec section 4.1) — the classic
// marketing homepage with featured listings moved to /listings.
Route::get('/', [ChatController::class, 'index'])->name('home');
Route::get('/listings', [HomeController::class, 'index'])->name('listings');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/investment-guide', [PageController::class, 'investmentGuide'])->name('investment-guide');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-conditions', [PageController::class, 'terms'])->name('terms');

// Properties
Route::prefix('properties')->group(function () {
    Route::get('/', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('/search', [PropertyController::class, 'search'])->name('properties.search');
    Route::get('/{slug}', [PropertyController::class, 'show'])->name('properties.show');
});

// Projects
Route::prefix('projects')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/{slug}', [ProjectController::class, 'show'])->name('projects.show');
});

// Resale
Route::get('/resale', [PropertyController::class, 'resale'])->name('resale.index');
Route::get('/areas/{area}', [PropertyController::class, 'area'])->name('properties.area');

// Daily Rentals
Route::prefix('daily-rentals')->group(function () {
    Route::get('/', [RentalController::class, 'daily'])->name('rentals.daily');
    Route::get('/{slug}', [RentalController::class, 'showDaily'])->name('rentals.daily.show');
});

// Long-term rentals
Route::prefix('long-term-rentals')->group(function () {
    Route::get('/', [RentalController::class, 'longTerm'])->name('rentals.longterm');
    Route::get('/{slug}', [RentalController::class, 'showLongTerm'])->name('rentals.longterm.show');
});

// Blog
Route::prefix('blog')->group(function () {
    Route::get('/', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
    Route::get('/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');
});

// Agents
Route::prefix('agents')->group(function () {
    Route::get('/', [AgentController::class, 'index'])->name('agents.index');
    Route::get('/{id}', [AgentController::class, 'show'])->name('agents.show');
});

// Lead / Contact forms
Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
Route::post('/contact', [LeadController::class, 'contact'])->name('contact.send');
Route::post('/conversation-password/forgot', [LeadPasswordController::class, 'request'])->middleware('throttle:5,1')->name('lead.password.request');
Route::get('/conversation-password/reset/{token}', [LeadPasswordController::class, 'edit'])->name('lead.password.edit');
Route::post('/conversation-password/reset', [LeadPasswordController::class, 'update'])->middleware('throttle:5,1')->name('lead.password.update');

// Auth routes (Breeze)
require __DIR__.'/auth.php';
