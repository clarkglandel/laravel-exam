<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MovieApiController;
use App\Http\Controllers\Api\FavoriteController;

Route::prefix('movies')->controller(MovieApiController::class)->group(function () {
    Route::get('/search', 'search'); // /api/movies/search - now uses custom rate limiting
    Route::get('/{id}', 'movie');    // /api/movies/{id}
    Route::get('/recommendations/{genre}', 'recommendations'); // /api/movies/recommendations/{genre}
});

Route::prefix('favorites')->controller(FavoriteController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/check/{imdbId}', 'check');
    Route::delete('/{imdbId}', 'destroy');
    Route::post('/toggle', 'toggle');
});