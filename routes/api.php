<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureOwnerOrAdmin;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\VacationSpotController;

Route::prefix('users')
    ->controller(UserController::class)
    ->group(function () {
        Route::get('/', 'index')->name('users.index');
        Route::get('/{user}', 'show')->name('users.show');

        Route::middleware([
            'auth:api',
            EnsureOwnerOrAdmin::class
        ])->group(function () {
            Route::put('/{user}', 'update')->name('users.update');
            Route::delete('/{user}', 'destroy')->name('users.destroy');
        });
    });

Route::controller(AuthController::class)
    ->group(function () {
        Route::post('/register', 'register')->name('register');
        Route::post('/login', 'login')->name('login');

        Route::middleware('auth:api')->group(function () {
            Route::post('/logout', 'logout')->name('logout');
            Route::post('/refresh', 'refresh')->name('refresh');
            Route::get('/me', 'me')->name('me');
        });
    });

Route::prefix('vacation_spots')
    ->controller(VacationSpotController::class)->group(function () {
        Route::get('/', 'index')->name('vacation_spots.index');
        Route::get('/{vacation_spot}', 'show')->name('vacation_spot.show');

        Route::middleware([
            'auth:api',
            EnsureUserIsAdmin::class
        ])->group(function () {
            Route::post('/', 'store')->name('vacation_spots.store');
            Route::put('/{vacation_spot}', 'update')->name('vacation_spots.update');
            Route::delete('/{vacation_spot}', 'destroy')->name('vacation_spot.destroy');
        });
    });

Route::prefix('wishlists')
    ->controller(WishlistController::class)
    ->middleware('auth:api')
    ->group(function () {
        Route::get('/', 'index')->name('wishlists.index');
        Route::post('/', 'store')->name('wishlists.store');

        Route::middleware(EnsureOwnerOrAdmin::class)->group(function () {
            Route::get('/{wishlist}', 'show')->name('wishlists.show');
            Route::delete('/{wishlist}', 'destroy')->name('wishlists.destroy');
        });
    });

