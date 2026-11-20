<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionReportController extends Controller
{
    public function index(Request $request): View
    {
        [$startDate, $endDate, $search] = $this->validatedFilters($request);

        // Query utama dipakai bersama oleh ringkasan dan daftar transaksi.
        $ordersQuery = $this->ordersQuery($startDate, $endDate, $search);

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
            'endDate',
            'search'
        ));
    }

    /**
     * Unduh seluruh transaksi yang cocok dengan filter dalam format Excel.
     */
    public function export(Request $request): BinaryFileResponse
    {
        return $this->downloadExport($request, false);
    }

    /**
     * Unduh seluruh transaksi yang cocok dengan filter dalam format CSV.
     */
    public function exportCsv(Request $request): BinaryFileResponse
    {
        return $this->downloadExport($request, true);
    }

    private function downloadExport(Request $request, bool $asCsv): BinaryFileResponse
    {
        [$startDate, $endDate, $search] = $this->validatedFilters($request);

        $query = $this->ordersQuery($startDate, $endDate, $search)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $extension = $asCsv ? 'csv' : 'xlsx';
        $filename = 'laporan-transaksi-' . now()->format('Ymd-His') . '.' . $extension;

        return Excel::download(
            new TransactionsExport($query, $startDate, $endDate, $asCsv, $search),
            $filename,
            $asCsv ? ExcelFormat::CSV : ExcelFormat::XLSX
        );
    }

    /**
     * Validasi filter periode & pencarian (dipakai bersama oleh index dan export).
     *
     * @return array{0: ?string, 1: ?string, 2: string}
     */
    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'search' => ['nullable', 'string', 'max:100'],
        ], [
            'start_date.date_format' => 'Tanggal mulai tidak valid.',
            'end_date.date_format' => 'Tanggal akhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.',
            'search.max' => 'Pencarian maksimal 100 karakter.',
        ]);

        return [
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
            trim($validated['search'] ?? ''),
        ];
    }

    /**
     * Query transaksi dengan filter periode & pencarian (dipakai bersama oleh index dan export).
     */
    private function ordersQuery(?string $startDate, ?string $endDate, string $search = ''): Builder
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

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($memberQuery) use ($search) {
                        $memberQuery->where('name', 'like', "%{$search}%");
                    });

                if (str_contains('pembeli umum', mb_strtolower($search))) {
                    $builder->orWhereNull('member_id');
                }
            });
        }

        return $query;
    }
}
