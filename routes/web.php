<?php

use App\Http\Controllers\Admin\OutreachController;
use App\Http\Controllers\Webhook\ResendWebhookController;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\BusinessProfile\BusinessProfileController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\FeedbackController;
use App\Http\Controllers\Public\RatingController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReviewRequest\ReviewRequestController;
use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureTenantAccess;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy', fn () => view('legal.privacy'))->name('privacy');
Route::get('/terms', fn () => view('legal.terms'))->name('terms');

Route::get('/sitemap.xml', function () {
    $posts = collect(glob(resource_path('views/blog/*.blade.php')))
        ->map(fn ($path) => basename($path, '.blade.php'))
        ->reject(fn ($name) => $name === 'index');

    return response()->view('sitemap', ['posts' => $posts])->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/tools/google-review-link-generator', fn () => view('tools.google-review-link-generator'))->name('tools.google-review-link-generator');

Route::match(['get', 'post'], '/outreach/unsubscribe', function (\Illuminate\Http\Request $request) {
    if (! $request->hasValidSignature()) {
        abort(403);
    }
    $email = $request->query('email');
    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Mark in database
        \App\Infrastructure\Persistence\Eloquent\OutreachLeadModel::where('email', $email)
            ->update(['outreach_status' => 'unsubscribed']);
        // Also keep flat file log for the old CSV-based commands
        $path = 'outreach_unsubscribed.log';
        $existing = \Illuminate\Support\Facades\Storage::disk('local')->exists($path)
            ? array_filter(explode("\n", \Illuminate\Support\Facades\Storage::disk('local')->get($path)))
            : [];
        if (! in_array($email, $existing)) {
            \Illuminate\Support\Facades\Storage::disk('local')->append($path, $email);
        }
    }

    return response('<html><body style="font-family:Arial,sans-serif;max-width:500px;margin:80px auto;text-align:center;"><h2>You\'ve been unsubscribed</h2><p>You won\'t receive any more emails from us.</p></body></html>', 200)
        ->header('Content-Type', 'text/html');
})->name('outreach.unsubscribe');

Route::get('/blog', fn () => view('blog.index'))->name('blog.index');
Route::get('/blog/{slug}', function (string $slug) {
    if (! view()->exists("blog.{$slug}")) {
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

// Admin routes
Route::middleware(['auth', 'verified', EnsureAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/outreach', [OutreachController::class, 'index'])->name('outreach.index');
    Route::get('/outreach/leads', [OutreachController::class, 'leads'])->name('outreach.leads');
    Route::post('/outreach/run-weekly', [OutreachController::class, 'runWeekly'])->name('outreach.run-weekly');
    Route::post('/outreach/send-batch', [OutreachController::class, 'sendBatch'])->name('outreach.send-batch');
    Route::post('/outreach/send-test', [OutreachController::class, 'sendTest'])->name('outreach.send-test');
    Route::patch('/outreach/leads/{id}/status', [OutreachController::class, 'updateStatus'])->name('outreach.update-status');
});

// Webhooks (no CSRF, rate limited)
Route::post('/webhooks/resend', ResendWebhookController::class)
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
    ->middleware('throttle:60,1')
    ->name('webhooks.resend');

require __DIR__.'/auth.php';
