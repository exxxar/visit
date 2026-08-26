<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\PlaceMenuController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\StoryController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('feedback', [FeedbackController::class, 'store'])
        ->middleware('throttle:5,1'); // 5 запросов в минуту с IP

    // справочники
    Route::get('districts', [DistrictController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);

    // заведения
    Route::get('places', [PlaceController::class, 'index']);
    Route::get('places/{slug}', [PlaceController::class, 'show']);
    Route::post('places/{place:slug}/reviews', [ReviewController::class, 'store'])
        ->middleware('throttle:public');

    Route::get('places/{place}/menu', [PlaceMenuController::class, 'show'])
        ->middleware('throttle:public');

    // контент
    Route::get('events', [EventController::class, 'index']);

    Route::post('events', [EventController::class, 'store'])
        ->middleware('throttle:public');

    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{slug}', [PostController::class, 'show']);

    // заявки и лиды (лендинг)
    Route::post('applications', [ApplicationController::class, 'store'])->middleware('throttle:public');
    Route::post('leads', [LeadController::class, 'store'])->middleware('throttle:public');
    Route::post('subscribe', [SubscriptionController::class, 'store'])->middleware('throttle:public');
    Route::get('subscribe/{token}/cancel', [SubscriptionController::class, 'cancel'])->name('subscribe.cancel');


    Route::get('stories', [StoryController::class, 'actual']);
    Route::get('stories/archive', [StoryController::class, 'archive']);
    Route::post('stories/{story}/view', [StoryController::class, 'view'])->middleware('throttle:public');

    // личный кабинет (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('favorites/{place}', [FavoriteController::class, 'toggle']);
    });
});
