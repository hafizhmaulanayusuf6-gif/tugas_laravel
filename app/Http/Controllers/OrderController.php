<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Product;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category')->orderBy('name')->paginate(12);

        // Hanya member aktif yang bisa dipilih sebagai pelanggan.
        $members = Member::where('status', 'aktif')
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('orders.index', compact('products', 'members'));
    }
}