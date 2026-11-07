<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');

Route::view('/dashboard', 'dashboard.index')->name('dashboard.index');
Route::view('/orders', 'orders.index')->name('orders.index');
Route::view('/history', 'history.index')->name('history.index');

Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
Route::resource('members', MemberController::class)->except(['create', 'edit', 'show']);
Route::resource('products', ProductController::class)->except(['create', 'edit', 'show']);
Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);