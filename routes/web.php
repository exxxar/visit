<?php

use App\Http\Controllers\Account\DevelopmentController;
use App\Http\Controllers\Account\FavoritesController;
use App\Http\Controllers\Account\MyReviewsController;
use App\Http\Controllers\Account\PromotionController;
use App\Http\Controllers\Account\TouristDashboardController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Account;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\PartnerNetworkController;
use App\Http\Controllers\Admin\PartnerRequestController;
use App\Http\Controllers\Admin\PartnerTransactionController;
use App\Http\Controllers\Admin\PartnerWithdrawalController;
use App\Http\Controllers\Admin\PlaceTenantController;
use App\Http\Controllers\Admin\ReviewModerationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AfishaController;
use App\Http\Controllers\Api\PlaceFavoriteController;
use App\Http\Controllers\Api\PlaceReportController;
use App\Http\Controllers\Api\PlaceReviewController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Dashboard\TenantCreationController;
use App\Http\Controllers\EventPageController;
use App\Http\Controllers\OutboundLinkController;
use App\Http\Controllers\PlacePageController;
use App\Http\Controllers\PostPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\PartnerController;
use App\Http\Controllers\ReferralController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* ==========================================================================
   1. ПУБЛИЧНАЯ ЧАСТЬ (Без middleware auth)
   ========================================================================== */
Route::get('/', App\Http\Controllers\LandingController::class)->name('landing');

Route::get('/out', [OutboundLinkController::class, 'redirect'])->name('out');

Route::get('/partners', [PartnerController::class, 'index'])->name('partners.index');
Route::get('/partner/{slug}', [PartnerController::class, 'show'])->name('partner.public');

Route::get('/news', [App\Http\Controllers\NewsController::class, 'index'])->name('news.index');
Route::get('/news/{news}', [App\Http\Controllers\NewsController::class, 'show'])->name('news.show');

Route::get('/places', [CatalogController::class, 'index'])->name('catalog');
Route::get('/place/{place:slug}', [PlacePageController::class, 'show'])->name('place.show');
Route::get('/post/{post:slug}', [PostPageController::class, 'show'])->name('post.show');
Route::get('/event/{event:slug}', [EventPageController::class, 'show'])->name('event.show');
Route::get('/afisha', [AfishaController::class, 'index'])->name('afisha');

// Публичные отзывы (только просмотр)
Route::prefix('places/{place}/reviews')->group(function () {
    Route::get('/', [PlaceReviewController::class, 'index']);
});

Route::prefix('ref')->group(function () {
    Route::get('/choose', [ReferralController::class, 'choose'])->name('ref.choose');
    Route::get('/client', [ReferralController::class, 'clientForm'])->name('ref.client');
    Route::get('/tech', [ReferralController::class, 'techForm'])->name('ref.tech');
    Route::get('/ad', [ReferralController::class, 'adForm'])->name('ref.ad');
    Route::get('/promote', [ReferralController::class, 'promoteForm'])->name('ref.promote');

    Route::post('/client', [ReferralController::class, 'registerClient'])->name('ref.client.store');
    Route::post('/tech', [ReferralController::class, 'registerTech'])->name('ref.tech.store');
    Route::post('/ad', [ReferralController::class, 'registerAd'])->name('ref.ad.store');
    Route::post('/promote', [ReferralController::class, 'registerPromote'])->name('ref.promote.store');
});

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.store');
});

/* ==========================================================================
   2. ДЕЙСТВИЯ ДЛЯ АВТОРИЗОВАННЫХ (Избранное, Жалобы, Действия с отзывами)
   ========================================================================== */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/places/{place}/favorite', [PlaceFavoriteController::class, 'toggle']);
    Route::get('/places/{place}/favorite/check', [PlaceFavoriteController::class, 'check']);
    Route::post('/places/report', [PlaceReportController::class, 'store']);

    Route::prefix('places/{place}/reviews')->group(function () {
        Route::post('/', [PlaceReviewController::class, 'store']);
        Route::put('/{review}', [PlaceReviewController::class, 'update']);
        Route::delete('/{review}', [PlaceReviewController::class, 'destroy']);
        Route::post('/{review}/reply', [PlaceReviewController::class, 'reply']);
        Route::post('/{review}/report', [PlaceReviewController::class, 'report']);
    });
});

/* ==========================================================================
   3. ДАШБОРД-ХАБ (Развод по ролям после логина)
   ========================================================================== */
Route::middleware('auth')->get('/dashboard', function (Request $request) {
    $user = $request->user();

    return redirect(match (true) {
        $user->hasAnyRole(['admin', 'moderator', 'editor']) => '/admin',
        $user->hasAnyRole(['business', 'tech_partner', 'ad_partner']) => '/account',
        default => '/account', // Туристы (role:user) тоже идут в /account
    });
})->name('dashboard');

/* ==========================================================================
   4. КАБИНЕТ ТУРИСТА (Только роль user)
   ========================================================================== */
Route::middleware(['auth', 'verified', 'role:user'])
    ->prefix('account')->name('account.')
    ->group(function () {
        Route::get('/', [TouristDashboardController::class, 'index'])->name('dashboard');
        Route::get('/favorites', [FavoritesController::class, 'index'])->name('favorites');
        Route::get('/reviews', [MyReviewsController::class, 'index'])->name('reviews');
    });

/* ==========================================================================
   5. КАБИНЕТ ЗАВЕДЕНИЯ / ПАРТНЕРА (business, tech_partner, ad_partner, admin)
   ========================================================================== */
Route::middleware(['auth', 'verified', 'role:business|tech_partner|ad_partner|admin'])
    ->prefix('account')->name('account.')
    ->group(function () {
        Route::get('/', Account\DashboardController::class)->name('dashboard');

        Route::get('review/{type}/{id}', [Account\ReviewController::class, 'create'])->name('review.create');
        Route::post('review', [Account\ReviewController::class, 'store'])->name('review.store');

        Route::get('referrals', [Account\ReferralController::class, 'index'])->name('referrals.index');
        Route::get('partner/wallet', [Account\PartnerWalletController::class, 'index'])->name('partner.wallet');
        Route::post('partner/withdraw', [Account\PartnerWalletController::class, 'withdraw'])->name('partner.withdraw');

        // Технический партнёр
        Route::middleware('partner:tech_partner')->prefix('development')->name('development.')->group(function () {
            Route::get('/', [DevelopmentController::class, 'index'])->name('index');
            Route::post('{development}/claim', [DevelopmentController::class, 'claim'])->name('claim');
            Route::post('{development}/status', [DevelopmentController::class, 'updateStatus'])->name('status');
        });

        // Рекламный партнёр
        Route::middleware('partner:ad_partner')->prefix('promotions')->name('promotions.')->group(function () {
            Route::get('/', [PromotionController::class, 'index'])->name('index');
            Route::post('{promotion}/claim', [PromotionController::class, 'claim'])->name('claim');
            Route::post('{promotion}/status', [PromotionController::class, 'updateStatus'])->name('status');
        });

        Route::post('places/{place}/promote', [Account\PlaceController::class, 'promote'])->name('places.promote');
        Route::get('/place/{place:slug}', [Account\PlaceController::class, 'show'])->name('place.show');
        Route::get('places/{place:slug}/edit', [Account\PlaceController::class, 'edit'])->name('places.edit');
        Route::put('places/{place:slug}', [Account\PlaceController::class, 'update'])->name('places.update');
        Route::post('places/{place:slug}/photos', [Account\PlaceController::class, 'addPhoto'])->name('places.photos');
        Route::get('places/{place:slug}/analytics', [Account\AnalyticsController::class, 'show'])->name('analytics');

        Route::get('stories', [Account\StoryController::class, 'index'])->name('stories.index');
        Route::post('stories', [Account\StoryController::class, 'store'])->name('stories.store');
        Route::delete('stories/{story}', [Account\StoryController::class, 'destroy'])->name('stories.destroy');

        Route::get('news', [Account\NewsController::class, 'index'])->name('news.index');
        Route::post('news', [Account\NewsController::class, 'store'])->name('news.store');
        Route::delete('news/{news}', [Account\NewsController::class, 'destroy'])->name('news.destroy');
    });

/* ==========================================================================
   6. АДМИН-ПАНЕЛЬ
   ========================================================================== */
Route::middleware(['auth', 'verified', 'admin.access'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::get('referrals', [ReferralController::class, 'index'])->name('referrals.index');
        Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
        Route::get('outbound', [Admin\OutboundController::class, 'index'])->name('outbound.index');

        // Карта
        Route::prefix('map')->name('map.')->group(function () {
            Route::get('editor', [Admin\MapObjectController::class, 'editor'])->name('editor');
            Route::resource('layers', Admin\MapLayerController::class)->except(['show', 'edit', 'update']);
            Route::post('layers/reorder', [Admin\MapLayerController::class, 'reorder'])->name('layers.reorder');
            Route::resource('objects', Admin\MapObjectController::class)->except(['show', 'edit', 'update']);
            Route::post('objects/reorder', [Admin\MapObjectController::class, 'reorder'])->name('objects.reorder');
        });

        // Модерация отзывов
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [Admin\ReviewController::class, 'index'])->name('index');
            Route::post('{review}/toggle', [Admin\ReviewController::class, 'togglePublic'])->name('toggle');
            Route::post('{review}/verify', [Admin\ReviewController::class, 'verify'])->name('verify');
            Route::delete('{review}', [Admin\ReviewController::class, 'destroy'])->name('destroy');
            Route::post('{review}/note', [Admin\ReviewController::class, 'addNote'])->name('note');

            Route::get('moderation', [ReviewModerationController::class, 'index'])->name('moderation');
            Route::post('{review}/approve', [ReviewModerationController::class, 'approve'])->name('approve');
            Route::post('{review}/reject', [ReviewModerationController::class, 'reject'])->name('reject');
        });

        // Партнерская система
        Route::prefix('partner')->name('partner.')->group(function () {
            Route::get('withdrawals', [PartnerWithdrawalController::class, 'index'])->name('withdrawals.index');
            Route::post('withdrawals/{withdrawal}/approve', [PartnerWithdrawalController::class, 'approve'])->name('withdrawals.approve');
            Route::post('withdrawals/{withdrawal}/reject', [PartnerWithdrawalController::class, 'reject'])->name('withdrawals.reject');
            Route::get('transactions', [PartnerTransactionController::class, 'index'])->name('transactions.index');
            Route::get('requests', [PartnerRequestController::class, 'index'])->name('requests.index');
            Route::post('requests/{request}/status', [PartnerRequestController::class, 'updateStatus'])->name('requests.status');
            Route::get('network', [PartnerNetworkController::class, 'index'])->name('network.index');
        });

        // Общая модерация
        Route::prefix('moderation')->name('moderation.')->controller(Admin\ModerationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('{type}/{id}', 'act')->name('act')->where('type', 'places|news|events|reviews|applications');
            Route::post('import/mypwa', 'importMypwa')->name('import.mypwa');
        });

        // Заведения
        Route::resource('places', Admin\PlaceController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::post('/places/{place}/create-tenant', [PlaceTenantController::class, 'store'])->name('places.create-tenant');
        Route::put('places/{place}/rating', [Admin\PlaceController::class, 'updateRating'])->name('places.rating');
        Route::post('places/{place}/recalculate-rating', [Admin\PlaceController::class, 'recalculateRating'])->name('places.recalculate-rating');
        Route::post('places/{place}/photos', [Admin\PlaceController::class, 'addPhoto']);
        Route::post('places/{place}/featured', [Admin\PlaceController::class, 'toggleFeatured'])->name('places.featured');
        Route::get('places/{place}/analytics', [Admin\PlaceController::class, 'analytics'])->name('places.analytics');

        // Контент
        Route::resource('events', EventController::class)->except(['show']);
        Route::resource('news', NewsController::class)->except(['show']);
        Route::resource('posts', Admin\PostController::class);
        Route::resource('placements', Admin\PlacementController::class)->except(['show', 'edit']);
        Route::get('stories', [Admin\StoryController::class, 'index'])->name('stories.index');
        Route::post('stories', [Admin\StoryController::class, 'store'])->name('stories.store');
        Route::post('stories/{story}/act', [Admin\StoryController::class, 'act'])->name('stories.act');
        Route::delete('stories/{story}', [Admin\StoryController::class, 'destroy'])->name('stories.destroy');

        // Пользователи и роли
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::put('users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password');
        Route::post('users/{user}/reset-link', [UserController::class, 'sendResetLink'])->name('users.reset-link');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('roles', [Admin\RoleController::class, 'index'])->name('roles.index');
        Route::put('roles/{role}', [Admin\RoleController::class, 'update'])->name('roles.update');

        // Обратная связь и Лиды
        Route::resource('feedback', Admin\FeedbackController::class)->only(['index', 'update', 'destroy']);
        Route::post('feedback/{feedback}/reply', [Admin\FeedbackController::class, 'reply'])->name('feedback.reply');
        Route::get('leads', [Admin\LeadController::class, 'index'])->name('leads.index');
        Route::get('leads/export', [Admin\LeadController::class, 'export'])->name('leads.export');

        // Настройки
        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/recalculate-districts', [Admin\SettingController::class, 'recalculateDistricts'])->name('settings.recalculate-districts');

        // Тенанты
        Route::post('/dashboard/tenants/create', [TenantCreationController::class, 'store'])->name('dashboard.tenants.create');
    });

/* ==========================================================================
   7. ПРОФИЛЬ (Breeze)
   ========================================================================== */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
