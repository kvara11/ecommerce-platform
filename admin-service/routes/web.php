<?php

use App\Http\Controllers\UserViewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductsViewController;
use App\Http\Controllers\CategoriesViewController;
use App\Http\Controllers\OrdersViewController;
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

    Route::get('/products', [ProductsViewController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductsViewController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductsViewController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductsViewController::class, 'destroy'])->name('products.destroy');

    Route::get('/categories', [CategoriesViewController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoriesViewController::class, 'store'])->name('categories.store');
    Route::put('/categories/toggle-status/{category}', [CategoriesViewController::class, 'toggleStatus'])->name('categories.toggleStatus');
    Route::put('/categories/{category}', [CategoriesViewController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoriesViewController::class, 'destroy'])->name('categories.destroy');

    Route::get('/orders', [OrdersViewController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrdersViewController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}', [OrdersViewController::class, 'update'])->name('orders.update');

});
