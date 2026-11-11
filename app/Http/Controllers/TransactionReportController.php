<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ], [
            'start_date.date_format' => 'Tanggal mulai tidak valid.',
            'end_date.date_format' => 'Tanggal akhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.',
        ]);

        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        // Query utama dipakai bersama oleh ringkasan dan daftar transaksi.
        $ordersQuery = Order::query()->with([
            'member:id,name',
            'items:id,order_id,quantity',
        ]);

        if ($startDate) {
            $ordersQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $ordersQuery->whereDate('created_at', '<=', $endDate);
        }

        // Ringkasan dihitung dari transaksi yang sama dengan filter tabel.
        $totalTransactions = (clone $ordersQuery)->count();
        $totalSales = (clone $ordersQuery)->sum('total');
        $averageTransaction = (clone $ordersQuery)->avg('total') ?? 0;

        // Jumlah unit terjual berasal dari detail transaksi, bukan jumlah baris produk.
        $totalItemsSold = OrderItem::query()
            ->whereIn('order_id', (clone $ordersQuery)->select('orders.id'))
            ->sum('quantity');

        $orders = $ordersQuery
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('reports.transactions', compact(
            'orders',
            'totalTransactions',
            'totalSales',
            'totalItemsSold',
            'averageTransaction',
            'startDate',
            'endDate'
        ));
    }
}
