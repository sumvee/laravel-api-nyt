<?php

use App\Http\Controllers\BestSellersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    Route::get('/nyt/best-sellers', [BestSellersController::class, 'fetchBestSellers'])
        ->middleware('throttle:api');
});



