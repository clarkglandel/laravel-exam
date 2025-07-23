<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MovieApiController;

Route::get('/', function () {
    return view('welcome');
});

// Catch-all route for SPA - must be last
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
