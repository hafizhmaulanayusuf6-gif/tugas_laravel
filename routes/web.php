<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');

Route::view('/dashboard', 'dashboard.index')->name('dashboard.index');
Route::view('/members', 'members.index')->name('members.index');
Route::view('/categories', 'categories.index')->name('categories.index');
Route::view('/users', 'users.index')->name('users.index');

Route::resource('products', ProductController::class)->only(['index', 'store']);
