<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\TransactionReportController;
use App\Http\Controllers\MemberReportController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route(Auth::user()->role === 'admin' ? 'dashboard.index' : 'orders.index');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // route khusu admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/reports/transactions', [TransactionReportController::class, 'index'])->name('reports.transactions');
        Route::get('/reports/products', [ProductReportController::class, 'index'])->name('reports.products');
        Route::get('/reports/members', [MemberReportController::class, 'index'])->name('reports.members');
        Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

        Route::resource('members', MemberController::class)->except(['create', 'edit', 'show']);
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
    });

    // route untuk admin dan kasir
    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

        Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
        Route::resource('products', ProductController::class)->except(['create', 'edit', 'show']);
    });
});

require __DIR__.'/auth.php';
