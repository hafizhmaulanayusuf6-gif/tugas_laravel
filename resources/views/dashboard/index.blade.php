@extends('templates.layoutApp')

@section('content')
  <style>
    .dashboard-page { --dashboard-border: var(--bs-border-color); --dashboard-muted: var(--bs-secondary-color); }
    .dashboard-page .dashboard-header { padding-block: 1.25rem; }
    .dashboard-page .dashboard-card { border: 1px solid var(--dashboard-border); border-radius: .75rem; box-shadow: 0 .125rem .5rem rgb(0 0 0 / 3%); }
    .dashboard-page .dashboard-card .card-header { padding: 1rem 1.125rem .75rem; }
    .dashboard-page .dashboard-card .card-body { padding: 1rem 1.125rem; }
    .dashboard-page .dashboard-stat { min-height: 112px; border: 1px solid var(--dashboard-border); border-radius: .75rem; box-shadow: 0 .125rem .5rem rgb(0 0 0 / 3%); }
    .dashboard-page .dashboard-stat .info-box-icon { width: 2.75rem; height: 2.75rem; flex: 0 0 2.75rem; font-size: 1.15rem; color: var(--bs-primary); background: color-mix(in srgb, var(--bs-primary) 9%, var(--bs-body-bg)); }
    .dashboard-page .info-box-content { min-width: 0; }
    .dashboard-page .info-box-text { font-size: .8125rem; }
    .dashboard-page .info-box-number { display: block; margin-block: .125rem; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
    .dashboard-page .chart-wrap { min-height: 210px; }
    .dashboard-page .sales-chart { display: block; width: 100%; height: auto; min-height: 190px; }
    .dashboard-page .table th, .dashboard-page .table td { padding-block: .8rem; }
    .dashboard-page .table thead th { color: var(--dashboard-muted); font-size: .75rem; font-weight: 600; letter-spacing: .025em; text-transform: uppercase; white-space: nowrap; }
    .dashboard-page .table tbody td { font-size: .875rem; }
    .dashboard-page .card-title { float: none; display: block; line-height: 1.3; }
    .dashboard-page .dashboard-link { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; line-height: 1.25; vertical-align: middle; }
    .dashboard-page .product-progress { height: 7px; min-width: 48px; background: var(--bs-tertiary-bg); }
    .dashboard-page .product-rank { width: 1.75rem; color: var(--dashboard-muted); font-variant-numeric: tabular-nums; }
    @media (max-width: 575.98px) {
      .dashboard-page .dashboard-header { padding-block: 1rem; }
      .dashboard-page .sales-chart { min-height: 165px; }
      .dashboard-page .dashboard-card .card-header, .dashboard-page .dashboard-card .card-body { padding-inline: .875rem; }
    }
  </style>

  <div class="dashboard-page">
    <div class="app-content-header dashboard-header">
      <div class="container-fluid">
        <div class="row align-items-center g-2">
          <div class="col-sm-7">
            <h1 class="mb-1 fs-3 fw-semibold">Dashboard</h1>
            <p class="text-muted mb-0">Ringkasan aktivitas dan performa toko.</p>
          </div>
          <div class="col-sm-5 text-sm-end">
            <span class="text-muted small"><i class="bi bi-calendar3 me-1" aria-hidden="true"></i>{{ $todayLabel }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="app-content pt-0">
      <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
          <span class="small text-muted">Kondisi toko hari ini</span>
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('orders.index') }}" class="btn btn-primary btn-sm dashboard-link"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Transaksi Baru</a>
            <a href="{{ route('reports.transactions') }}" class="btn btn-outline-secondary btn-sm dashboard-link"><i class="bi bi-file-earmark-bar-graph me-1" aria-hidden="true"></i>Laporan Transaksi</a>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-12 col-sm-6 col-xl-3"><div class="info-box dashboard-stat h-100 mb-0">
            <span class="info-box-icon rounded-2"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
            <div class="info-box-content"><span class="info-box-text text-muted">Omzet Hari Ini</span><span class="info-box-number fs-5">Rp {{ number_format($todaySales, 0, ',', '.') }}</span><span class="text-muted small">Dari transaksi hari ini</span></div>
          </div></div>
          <div class="col-12 col-sm-6 col-xl-3"><div class="info-box dashboard-stat h-100 mb-0">
            <span class="info-box-icon rounded-2"><i class="bi bi-receipt" aria-hidden="true"></i></span>
            <div class="info-box-content"><span class="info-box-text text-muted">Transaksi Hari Ini</span><span class="info-box-number fs-5">{{ number_format($todayTransactions, 0, ',', '.') }}</span><span class="text-muted small">Transaksi tercatat</span></div>
          </div></div>
          <div class="col-12 col-sm-6 col-xl-3"><div class="info-box dashboard-stat h-100 mb-0">
            <span class="info-box-icon rounded-2"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
            <div class="info-box-content"><span class="info-box-text text-muted">Total Produk</span><span class="info-box-number fs-5">{{ number_format($totalProducts, 0, ',', '.') }}</span><span class="text-muted small">Produk terdaftar</span></div>
          </div></div>
          <div class="col-12 col-sm-6 col-xl-3"><div class="info-box dashboard-stat h-100 mb-0">
            <span class="info-box-icon rounded-2"><i class="bi bi-people" aria-hidden="true"></i></span>
            <div class="info-box-content"><span class="info-box-text text-muted">Total Member</span><span class="info-box-number fs-5">{{ number_format($totalMembers, 0, ',', '.') }}</span><span class="text-muted small">Pelanggan terdaftar</span></div>
          </div></div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-12 col-xl-8">
            <section class="card dashboard-card h-100 mb-0" aria-labelledby="sales-chart-title">
              <div class="card-header border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div><h2 id="sales-chart-title" class="card-title fw-semibold fs-6 mb-0">Grafik Penjualan</h2><div class="text-muted small mt-1">Omzet selama tujuh hari terakhir</div></div>
                <span class="badge text-bg-light border">7 hari terakhir</span>
              </div>
              <div class="card-body pt-0">
                <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-2">
                  <div><div class="text-muted small">Total omzet 7 hari</div><div class="fs-4 fw-semibold">Rp {{ number_format($dailySales->sum('total'), 0, ',', '.') }}</div></div>
                  <div class="text-muted small"><span class="d-inline-block rounded-circle bg-primary me-1" style="width:8px;height:8px" aria-hidden="true"></span>Omzet harian</div>
                </div>
                <div class="chart-wrap w-100 overflow-hidden">
                  <svg viewBox="0 0 780 250" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Grafik omzet tujuh hari terakhir" class="sales-chart">
                    <defs><linearGradient id="salesBarGradient" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="var(--bs-primary)" stop-opacity=".88"/><stop offset="100%" stop-color="var(--bs-primary)" stop-opacity=".58"/></linearGradient></defs>
                    @foreach ($chartTicks as $tick)
                      <line x1="72" y1="{{ $tick['y'] }}" x2="752" y2="{{ $tick['y'] }}" stroke="currentColor" stroke-opacity=".12" stroke-dasharray="3 5" />
                      <text x="62" y="{{ $tick['y'] + 4 }}" text-anchor="end" font-size="11" fill="currentColor" fill-opacity=".68">{{ $tick['label'] }}</text>
                    @endforeach
                    <line x1="72" y1="205" x2="752" y2="205" stroke="currentColor" stroke-opacity=".22" />
                    @foreach ($chartBars as $bar)
                      <rect x="{{ $bar['bar_x'] }}" y="{{ $bar['bar_y'] }}" width="{{ $bar['bar_width'] }}" height="{{ max($bar['bar_height'], $bar['total'] > 0 ? 2 : 0) }}" rx="5" fill="url(#salesBarGradient)">
                        <title>{{ $bar['full_date'] }}: Rp {{ number_format($bar['total'], 0, ',', '.') }}</title>
                      </rect>
                      <text x="{{ $bar['x'] }}" y="230" text-anchor="middle" font-size="12" font-weight="600" fill="currentColor" fill-opacity=".8">{{ $bar['label'] }}</text>
                      <text x="{{ $bar['x'] }}" y="245" text-anchor="middle" font-size="10" fill="currentColor" fill-opacity=".62">{{ \Illuminate\Support\Carbon::parse($bar['date'])->format('d/m') }}</text>
                    @endforeach
                  </svg>
                </div>
                <p class="text-muted small mb-0 mt-2">Tanggal tanpa transaksi tetap ditampilkan dengan omzet Rp0.</p>
              </div>
            </section>
          </div>

          <div class="col-12 col-xl-4">
            <section class="card dashboard-card h-100 mb-0" aria-labelledby="out-of-stock-title">
              <div class="card-header border-0 d-flex justify-content-between align-items-center gap-2">
                <div><h2 id="out-of-stock-title" class="card-title fw-semibold fs-6 mb-0">Stok Habis</h2><div class="text-muted small mt-1">Produk yang perlu diisi ulang</div></div>
                <span class="badge {{ $outOfStockCount > 0 ? 'text-bg-danger' : 'text-bg-success' }}">{{ number_format($outOfStockCount, 0, ',', '.') }}</span>
              </div>
              <div class="card-body pt-0 d-flex flex-column">
                <div class="flex-grow-1">
                  @forelse ($outOfStockProducts as $product)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom gap-2"><span class="text-truncate" title="{{ $product->name }}">{{ $product->name }}</span><span class="badge text-bg-danger flex-shrink-0">Habis</span></div>
                  @empty
                    <div class="text-center text-muted py-4"><i class="bi bi-check-circle text-success fs-3 d-block mb-2" aria-hidden="true"></i>Tidak ada produk dengan stok habis.</div>
                  @endforelse
                  @if ($outOfStockCount > $outOfStockProducts->count())<div class="small text-muted mt-2">dan {{ $outOfStockCount - $outOfStockProducts->count() }} produk lainnya.</div>@endif
                </div>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary w-100 mt-3 dashboard-link">Kelola Produk</a>
              </div>
            </section>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-12 col-xxl-8">
            <section class="card dashboard-card h-100 mb-0" aria-labelledby="recent-orders-title">
              <div class="card-header border-0 d-flex justify-content-between align-items-center gap-2">
                <div><h2 id="recent-orders-title" class="card-title fw-semibold fs-6 mb-0">Transaksi Terbaru</h2><div class="text-muted small mt-1">Lima transaksi terakhir yang tercatat</div></div>
                <a href="{{ route('history.index') }}" class="btn btn-sm btn-outline-secondary dashboard-link">Lihat semua</a>
              </div>
              <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light"><tr><th class="ps-3">Invoice</th><th>Tanggal</th><th>Pelanggan</th><th class="text-end pe-3">Total</th></tr></thead>
                  <tbody>
                    @forelse ($recentOrders as $order)
                      <tr><td class="ps-3 fw-semibold text-nowrap">{{ $order->invoice_number ?? 'INV-' . $order->id }}</td><td class="text-nowrap">{{ $order->created_at->format('d/m/Y H:i') }}</td><td>{{ $order->member?->name ?? 'Pembeli umum' }}</td><td class="text-end pe-3 text-nowrap fw-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr>
                    @empty
                      <tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-receipt fs-4 d-block mb-2" aria-hidden="true"></i>Belum ada transaksi yang tercatat.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div></div>
            </section>
          </div>

          <div class="col-12 col-xxl-4">
            <section class="card dashboard-card h-100 mb-0" aria-labelledby="top-products-title">
              <div class="card-header border-0 d-flex justify-content-between align-items-center gap-2">
                <div><h2 id="top-products-title" class="card-title fw-semibold fs-6 mb-0">Produk Terlaris</h2><div class="text-muted small mt-1">Berdasarkan unit terjual</div></div>
                <a href="{{ route('reports.products') }}" class="btn btn-sm btn-outline-secondary dashboard-link">Laporan</a>
              </div>
              <div class="card-body">
                @forelse ($topProducts as $index => $product)
                  <div class="d-flex align-items-center gap-2 {{ $loop->last ? '' : 'mb-3' }}">
                    <span class="product-rank small fw-semibold">{{ $index + 1 }}.</span>
                    <span class="flex-grow-1 text-truncate small fw-medium" title="{{ $product->product_name }}">{{ $product->product_name }}</span>
                    <span class="small fw-semibold text-nowrap">{{ number_format($product->total_quantity, 0, ',', '.') }}</span>
                    <div class="progress product-progress" role="progressbar" aria-label="Penjualan {{ $product->product_name }}" aria-valuenow="{{ $product->bar_width }}" aria-valuemin="0" aria-valuemax="100" style="width: 20%;">
                      <div class="progress-bar bg-primary" style="width: {{ $product->bar_width }}%;"></div>
                    </div>
                  </div>
                @empty
                  <div class="text-center text-muted py-3">Belum ada data penjualan produk.</div>
                @endforelse
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
