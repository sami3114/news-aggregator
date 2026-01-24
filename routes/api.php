<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserFeedController;
use App\Http\Controllers\Api\UserPreferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/user', [AuthController::class, 'user'])->name('auth.user');
    });
});

// Public routes - Articles
Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/{article}', [ArticleController::class, 'show'])->name('articles.show');
});

// Public routes - Meta data
Route::get('/sources', [ArticleController::class, 'sources'])->name('sources.index');
Route::get('/categories', CategoryController::class)->name('categories.index');
Route::get('/authors', AuthorController::class)->name('authors.index');

// Protected routes - User preferences (requires authentication)
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/preferences', [UserPreferenceController::class, 'show'])->name('preferences.show');
        Route::post('/preferences', [UserPreferenceController::class, 'update'])->name('preferences.update');
        Route::get('/feed',UserFeedController::class)->name('feed');
    });

});
