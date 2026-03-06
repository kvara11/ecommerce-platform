<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TokenController;



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/token/generate', [TokenController::class, 'generate']);
    Route::post('/token/revoke-all', [TokenController::class, 'revokeAll']);
    Route::get('/tokens', [TokenController::class, 'list']);
});

