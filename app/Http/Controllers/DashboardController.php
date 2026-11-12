<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Gunakan timezone aplikasi agar rentang hari mengikuti waktu lokal toko.
        $today = CarbonImmutable::now(config('app.timezone'));
        $todayStart = $today->startOfDay();
        $todayEnd = $today->endOfDay();

        // Statistik transaksi hari ini menggunakan total penjualan, bukan uang diterima.
        $todayOrdersQuery = Order::query()
            ->whereBetween('created_at', [$todayStart, $todayEnd]);

        $todaySales = (clone $todayOrdersQuery)->sum('total');
        $todayTransactions = (clone $todayOrdersQuery)->count();

        // Statistik master data.
        $totalProducts = Product::query()->count();
        $totalMembers = Member::query()->count();
        $outOfStockCount = Product::query()->where('stock', 0)->count();

        // Ambil total omzet per tanggal untuk tujuh hari terakhir, termasuk hari ini.
        $chartStart = $today->subDays(6)->startOfDay();
        $dailyTotals = Order::query()
            ->selectRaw('DATE(created_at) as sales_date, SUM(total) as total_sales')
            ->whereBetween('created_at', [$chartStart, $todayEnd])
            ->groupByRaw('DATE(created_at)')
            ->orderBy('sales_date')
            ->pluck('total_sales', 'sales_date');

        $dayLabels = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $dailySales = collect();

        for ($date = $chartStart; $date->lte($today); $date = $date->addDay()) {
            $dateKey = $date->toDateString();

            $dailySales->push([
                'date' => $dateKey,
                'label' => $dayLabels[$date->dayOfWeek],
                'full_date' => $date->format('d/m/Y'),
                'total' => (int) ($dailyTotals[$dateKey] ?? 0),
            ]);
        }

        // Skala grafik dibulatkan ke angka yang mudah dibaca dan tetap aman saat belum ada transaksi.
        $maxDailySales = (int) $dailySales->max('total');
        $roughStep = max(1, $maxDailySales / 4);
        $magnitude = 10 ** floor(log10($roughStep));
        $normalizedStep = $roughStep / $magnitude;
        $niceFactor = match (true) {
            $normalizedStep <= 1 => 1,
            $normalizedStep <= 2 => 2,
            $normalizedStep <= 5 => 5,
            default => 10,
        };
        $axisStep = (int) ($niceFactor * $magnitude);
        $axisMax = max(1, $axisStep * 4);

        $plotLeft = 72;
        $plotRight = 744;
        $plotTop = 24;
        $plotBottom = 205;
        $plotHeight = $plotBottom - $plotTop;
        $pointCount = max($dailySales->count() - 1, 1);
        $barWidth = 44;

        $chartBars = $dailySales->values()->map(function (array $day, int $index) use (
            $plotLeft,
            $plotRight,
            $plotBottom,
            $plotHeight,
            $pointCount,
            $axisMax,
            $barWidth
        ): array {
            $x = $plotLeft + (($plotRight - $plotLeft) / 6) * $index;
            $barHeight = ($day['total'] / $axisMax) * $plotHeight;
            $y = $plotBottom - $barHeight;

            return [
                ...$day,
                'x' => round($x, 2),
                'bar_x' => round($x - ($barWidth / 2), 2),
                'bar_y' => round($y, 2),
                'bar_width' => $barWidth,
                'bar_height' => round($barHeight, 2),
            ];
        });

        $chartTicks = collect(range(0, 4))->map(function (int $step) use (
            $axisStep,
            $axisMax,
            $plotTop,
            $plotBottom
        ): array {
            $value = $axisStep * (4 - $step);
            $y = $plotTop + (($plotBottom - $plotTop) / 4) * $step;

            return [
                'value' => $value,
                'label' => $this->formatCompactNumber($value),
                'y' => round($y, 2),
            ];
        });

        // Lima transaksi terbaru; eager loading mencegah query berulang untuk member.
        $recentOrders = Order::query()
            ->with('member:id,name')
            ->latest('created_at')
            ->latest('id')
            ->limit(5)
            ->get(['id', 'invoice_number', 'member_id', 'total', 'created_at']);

        // Peringkat produk berdasarkan jumlah unit pada detail transaksi.
        $topProducts = OrderItem::query()
            ->select('product_id', 'product_name')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        $maxTopQuantity = max(1, (int) $topProducts->max('total_quantity'));
        $topProducts = $topProducts->map(function (OrderItem $item) use ($maxTopQuantity) {
            $item->bar_width = max(3, (int) round(($item->total_quantity / $maxTopQuantity) * 100));

            return $item;
        });

        // Tampilkan kondisi stok yang pasti: stok habis. Tidak menetapkan ambang stok rendah baru.
        $outOfStockProducts = Product::query()
            ->where('stock', 0)
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'stock']);

        return view('dashboard.index', [
            'todayLabel' => $today->locale('id')->translatedFormat('l, d F Y'),
            'todaySales' => (int) $todaySales,
            'todayTransactions' => (int) $todayTransactions,
            'totalProducts' => (int) $totalProducts,
            'totalMembers' => (int) $totalMembers,
            'outOfStockCount' => (int) $outOfStockCount,
            'dailySales' => $dailySales,
            'chartBars' => $chartBars,
            'chartTicks' => $chartTicks,
            'axisMax' => $axisMax,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'outOfStockProducts' => $outOfStockProducts,
        ]);
    }

    /** Format label sumbu grafik agar tidak terlalu panjang. */
    private function formatCompactNumber(int $value): string
    {
        if ($value >= 1_000_000) {
            $number = $value / 1_000_000;
            $formatted = rtrim(rtrim(number_format($number, 1, ',', '.'), '0'), ',');

            return $formatted . ' jt';
        }

        if ($value >= 1_000) {
            $number = $value / 1_000;
            $formatted = rtrim(rtrim(number_format($number, 1, ',', '.'), '0'), ',');

            return $formatted . ' rb';
        }

        return number_format($value, 0, ',', '.');
    }
}
