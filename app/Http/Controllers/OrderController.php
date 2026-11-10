<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category')->orderBy('name')->paginate(12);

        return view('orders.index', compact('products'));
    }
}