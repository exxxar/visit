<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Account;
use App\Http\Controllers\AfishaController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\EventPageController;
use App\Http\Controllers\PlacePageController;
use App\Http\Controllers\PostPageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* ---------- публичная часть ---------- */
Route::get('/', App\Http\Controllers\LandingController::class)->name('landing');

Route::get('/places', [CatalogController::class, 'index'])->name('catalog');
Route::get('/place/{place:slug}', [PlacePageController::class, 'show'])->name('place.show');
Route::get('/post/{post:slug}', [PostPageController::class, 'show'])->name('post.show');
Route::get('/event/{event:slug}', [EventPageController::class, 'show'])->name('event.show');
Route::get('/afisha', [AfishaController::class, 'index'])->name('afisha');

/* ---------- «хаб» вместо Breeze-dashboard: развоз по ролям ---------- */
Route::middleware('auth')->name('dashboard')->get('/dashboard', function (Request $request) {
    $user = $request->user();

    return redirect(match (true) {
        $user->hasAnyRole(['admin', 'moderator', 'editor']) => '/admin',
        $user->hasRole('business')                          => '/account',
        default                                             => '/',
    });
});

/* ---------- админка ---------- */
Route::middleware(['auth', 'verified', 'admin.access'])
    ->prefix('admin')->name('admin.')->group(function () {

        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::prefix('moderation')->name('moderation.')
            ->controller(Admin\ModerationController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('{type}/{id}', 'act')->name('act')
                    ->where('type', 'places|news|events|reviews|applications');

                // ИМПОРТ С MYPWA.RU
                Route::post('import/mypwa', 'importMypwa')->name('import.mypwa');
            });

        Route::resource('places', Admin\PlaceController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::post('places/{place}/photos', [Admin\PlaceController::class, 'addPhoto']);

        Route::post('places/{place}/featured', [Admin\PlaceController::class, 'toggleFeatured'])
            ->name('places.featured');
        Route::get('places/{place}/analytics', [Admin\PlaceController::class, 'analytics'])
            ->name('places.analytics');

        Route::get('stories', [Admin\StoryController::class, 'index'])->name('stories.index');
        Route::post('stories', [Admin\StoryController::class, 'store'])->name('stories.store');
        Route::post('stories/{story}/act', [Admin\StoryController::class, 'act'])->name('stories.act');
        Route::delete('stories/{story}', [Admin\StoryController::class, 'destroy'])->name('stories.destroy');

        Route::resource('users', Admin\UserController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::get('roles', [Admin\RoleController::class, 'index'])->name('roles.index');
        Route::put('roles/{role}', [Admin\RoleController::class, 'update'])->name('roles.update');

        Route::resource('posts', Admin\PostController::class);
        Route::resource('placements', Admin\PlacementController::class)->except(['show', 'edit']);

        Route::get('leads', [Admin\LeadController::class, 'index'])->name('leads.index');
        Route::get('leads/export', [Admin\LeadController::class, 'export'])->name('leads.export');

        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    });

/* ---------- кабинет заведения ---------- */
Route::middleware(['auth', 'verified', 'role:business|admin'])
    ->prefix('account')->name('account.')->group(function () {

        Route::get('/', Account\DashboardController::class)->name('dashboard');

        Route::get('/place/{place:slug}', [Account\PlaceController::class, 'show'])->name('place.show');
        Route::get('places/{place:slug}/edit',    [Account\PlaceController::class, 'edit'])->name('places.edit');
        Route::put('places/{place:slug}',         [Account\PlaceController::class, 'update'])->name('places.update');
        Route::post('places/{place:slug}/photos', [Account\PlaceController::class, 'addPhoto'])->name('places.photos');
        Route::get('places/{place:slug}/analytics', [Account\AnalyticsController::class, 'show'])->name('analytics');

        Route::get('stories', [Account\StoryController::class, 'index'])->name('stories.index');
        Route::post('stories', [Account\StoryController::class, 'store'])->name('stories.store');
        Route::delete('stories/{story}', [Account\StoryController::class, 'destroy'])->name('stories.destroy');

        Route::get('news',     [Account\NewsController::class, 'index'])->name('news.index');
        Route::post('news',    [Account\NewsController::class, 'store'])->name('news.store');
        Route::delete('news/{news}', [Account\NewsController::class, 'destroy'])->name('news.destroy');
    });

/* ---------- профиль (Breeze) ---------- */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
