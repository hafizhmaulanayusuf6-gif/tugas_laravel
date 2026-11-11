<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\TransactionReportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');

Route::view('/dashboard', 'dashboard.index')->name('dashboard.index');
Route::get('/reports/transactions', [TransactionReportController::class, 'index'])->name('reports.transactions');
Route::view('/reports/products', 'reports.products')->name('reports.products');
Route::view('/reports/members', 'reports.members')->name('reports.members');

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
Route::resource('members', MemberController::class)->except(['create', 'edit', 'show']);
Route::resource('products', ProductController::class)->except(['create', 'edit', 'show']);
Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);