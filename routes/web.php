<?php

use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\BusinessProfile\BusinessProfileController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\FeedbackController;
use App\Http\Controllers\Public\RatingController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReviewRequest\ReviewRequestController;
use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\EnsureTenantAccess;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy', fn () => view('legal.privacy'))->name('privacy');
Route::get('/terms', fn () => view('legal.terms'))->name('terms');

Route::get('/blog', fn () => view('blog.index'))->name('blog.index');
Route::get('/blog/{slug}', function (string $slug) {
    if (!view()->exists("blog.{$slug}")) {
        abort(404);
    }
    return view("blog.{$slug}");
})->where('slug', '[a-z0-9\-]+')->name('blog.show');

Route::post('/locale/{locale}', function (string $locale) {
    if (in_array($locale, config('locales.supported'))) {
        session()->put('locale', $locale);
    }
    return back();
})->name('locale.switch');

// Public routes (no auth)
Route::get('/rate/{slug}/{token}', [RatingController::class, 'show'])->name('rate.show');
Route::post('/rate/{slug}/{token}', [RatingController::class, 'store'])->name('rate.store');
Route::get('/rate/{slug}/{token}/feedback', [FeedbackController::class, 'showForm'])->name('rate.feedback');
Route::post('/rate/{slug}/{token}/feedback', [FeedbackController::class, 'store'])->name('rate.feedback.store');

// Authenticated & tenant-scoped routes
Route::middleware(['auth', 'verified', EnsureTenantAccess::class])->group(function () {
    // Billing (accessible even without active subscription)
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/billing/resume', [BillingController::class, 'resume'])->name('billing.resume');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');

    // App routes (require active subscription or trial)
    Route::middleware(EnsureActiveSubscription::class)->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('business-profiles', BusinessProfileController::class);

        Route::post('/business-profiles/{id}/review-requests', [ReviewRequestController::class, 'store'])
            ->name('review-requests.store');
        Route::post('/business-profiles/{id}/review-requests/bulk', [ReviewRequestController::class, 'bulk'])
            ->name('review-requests.bulk');

        Route::get('/business-profiles/{id}/qr-code', [QrCodeController::class, 'show'])
            ->name('qr-code.show');
        Route::get('/business-profiles/{id}/qr-code/download', [QrCodeController::class, 'download'])
            ->name('qr-code.download');

        Route::get('/business-profiles/{id}/feedback', [FeedbackController::class, 'index'])
            ->name('feedback.index');
    });
});

// User profile routes (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
