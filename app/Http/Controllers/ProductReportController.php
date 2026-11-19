<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $this->validatedFilters($request);
        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;
        $stockStatus = $validated['stock_status'] ?? 'all';

        // Query yang sama digunakan untuk ringkasan, tabel, dan export.
        $productsQuery = $this->productsQuery($categoryId, $stockStatus);

        $totalProducts = (clone $productsQuery)->count();
        $totalStock = (clone $productsQuery)->sum('stock');
        $outOfStockProducts = (clone $productsQuery)->where('stock', 0)->count();

        // Menggunakan harga jual saat ini, bukan harga modal atau nilai akuntansi persediaan.
        $potentialStockValue = (clone $productsQuery)->sum(DB::raw('price * stock'));

        $products = (clone $productsQuery)
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

    /** Unduh seluruh data yang cocok dengan filter, bukan hanya halaman aktif. */
    public function export(Request $request): BinaryFileResponse
    {
        $validated = $this->validatedFilters($request);
        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;
        $stockStatus = $validated['stock_status'] ?? 'all';

        $categoryName = $categoryId
            ? Category::query()->whereKey($categoryId)->value('name')
            : null;

        $query = $this->productsQuery($categoryId, $stockStatus)
            ->orderBy('name')
            ->orderBy('id');

        $filename = 'laporan-produk-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new ProductsExport($query, $categoryName, $stockStatus),
            $filename
        );
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'stock_status' => ['nullable', 'in:all,available,out_of_stock'],
        ], [
            'category_id.integer' => 'Kategori tidak valid.',
            'category_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'stock_status.in' => 'Filter ketersediaan stok tidak valid.',
        ]);
    }

    private function productsQuery(?int $categoryId, string $stockStatus): Builder
    {
        $query = Product::query()->with('category:id,name');

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        if ($stockStatus === 'available') {
            $query->where('stock', '>', 0);
        } elseif ($stockStatus === 'out_of_stock') {
            $query->where('stock', 0);
        }

        return $query;
    }
}
