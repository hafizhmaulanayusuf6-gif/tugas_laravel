<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionReportController extends Controller
{
    public function index(Request $request): View
    {
        [$startDate, $endDate] = $this->validatedPeriod($request);

        // Query utama dipakai bersama oleh ringkasan dan daftar transaksi.
        $ordersQuery = $this->ordersQuery($startDate, $endDate);

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

    /**
     * Unduh seluruh transaksi yang cocok dengan filter (bukan hanya satu halaman).
     */
    public function export(Request $request): BinaryFileResponse
    {
        [$startDate, $endDate] = $this->validatedPeriod($request);

        $query = $this->ordersQuery($startDate, $endDate)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $filename = 'laporan-transaksi-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new TransactionsExport($query, $startDate, $endDate), $filename);
    }

    /**
     * Validasi filter periode (dipakai bersama oleh index dan export).
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function validatedPeriod(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ], [
            'start_date.date_format' => 'Tanggal mulai tidak valid.',
            'end_date.date_format' => 'Tanggal akhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.',
        ]);

        return [$validated['start_date'] ?? null, $validated['end_date'] ?? null];
    }

    /**
     * Query transaksi dengan filter periode (dipakai bersama oleh index dan export).
     */
    private function ordersQuery(?string $startDate, ?string $endDate): Builder
    {
        $query = Order::query()->with([
            'member:id,name',
            'items:id,order_id,quantity',
        ]);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query;
    }
}
