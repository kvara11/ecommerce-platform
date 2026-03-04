<?php

use App\Http\Controllers\UserViewController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use Inertia\Inertia;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'admin',
    'throttle:admin',
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/users', [UserViewController::class, 'index'])->name('users.index');
    Route::post('/users', [UserViewController::class, 'store'])->name('users.store');
    Route::put('/users/toggle-status/{user}', [UserViewController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::put('/users/{user}', [UserViewController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserViewController::class, 'destroy'])->name('users.destroy');

});
