<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'stock_status' => ['nullable', 'in:all,available,out_of_stock'],
        ], [
            'category_id.integer' => 'Kategori tidak valid.',
            'category_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'stock_status.in' => 'Filter ketersediaan stok tidak valid.',
        ]);

        $categoryId = $validated['category_id'] ?? null;
        $stockStatus = $validated['stock_status'] ?? 'all';

        // Query yang sama digunakan untuk ringkasan dan tabel agar hasilnya konsisten.
        $productsQuery = Product::query()->with('category:id,name');

        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }

        if ($stockStatus === 'available') {
            $productsQuery->where('stock', '>', 0);
        } elseif ($stockStatus === 'out_of_stock') {
            $productsQuery->where('stock', 0);
        }

        $totalProducts = (clone $productsQuery)->count();
        $totalStock = (clone $productsQuery)->sum('stock');
        $outOfStockProducts = (clone $productsQuery)->where('stock', 0)->count();

        // Menggunakan harga jual saat ini, bukan harga modal atau nilai akuntansi persediaan.
        $potentialStockValue = (clone $productsQuery)->sum(DB::raw('price * stock'));

        $products = $productsQuery
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('reports.products', compact(
            'products',
            'categories',
            'totalProducts',
            'totalStock',
            'outOfStockProducts',
            'potentialStockValue',
            'categoryId',
            'stockStatus'
        ));
    }
}
