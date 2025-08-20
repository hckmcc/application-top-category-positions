<?php

use App\Http\Controllers\Api\AppTopCategoryController;
use App\Http\Middleware\RateLimitMiddleware;
use App\Http\Middleware\RequestResponseLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware([RateLimitMiddleware::class, RequestResponseLogger::class])->group(function () {
    Route::get('/appTopCategory', [AppTopCategoryController::class, 'getTopCategories']);
});
