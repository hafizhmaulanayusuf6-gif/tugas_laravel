@extends('templates.layoutApp')

@section('content')
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center g-2">
        <div class="col-sm-7">
          <h1 class="mb-1 fs-3 fw-semibold">Dashboard</h1>
          <p class="text-muted mb-0">Ringkasan aktivitas dan performa toko.</p>
        </div>
        <div class="col-sm-5 text-sm-end">
          <span class="text-muted small">
            <i class="bi bi-calendar3 me-1"></i>{{ $todayLabel }}
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      {{-- Quick actions --}}
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="small text-muted">Selamat datang. Berikut kondisi toko saat ini.</div>
        <div class="d-flex flex-wrap gap-2">
          <a href="{{ route('orders.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Transaksi Baru
          </a>
          <a href="{{ route('reports.transactions') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan Transaksi
          </a>
        </div>
      </div>

      {{-- Summary cards --}}
      <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="info-box shadow-sm h-100 mb-0">
            <span class="info-box-icon text-bg-success rounded-2">
              <i class="bi bi-cash-stack"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text text-muted">Omzet Hari Ini</span>
              <span class="info-box-number fs-5">Rp {{ number_format($todaySales, 0, ',', '.') }}</span>
              <span class="text-muted small">Dari transaksi hari ini</span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
          <div class="info-box shadow-sm h-100 mb-0">
            <span class="info-box-icon text-bg-primary rounded-2">
              <i class="bi bi-receipt"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text text-muted">Transaksi Hari Ini</span>
              <span class="info-box-number fs-4">{{ number_format($todayTransactions, 0, ',', '.') }}</span>
              <span class="text-muted small">Transaksi tercatat</span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
          <div class="info-box shadow-sm h-100 mb-0">
            <span class="info-box-icon text-bg-info rounded-2">
              <i class="bi bi-box-seam"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text text-muted">Total Produk</span>
              <span class="info-box-number fs-4">{{ number_format($totalProducts, 0, ',', '.') }}</span>
              <span class="text-muted small">Produk terdaftar</span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
          <div class="info-box shadow-sm h-100 mb-0">
            <span class="info-box-icon text-bg-warning rounded-2">
              <i class="bi bi-people"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text text-muted">Total Member</span>
              <span class="info-box-number fs-4">{{ number_format($totalMembers, 0, ',', '.') }}</span>
              <span class="text-muted small">Pelanggan terdaftar</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Sales chart --}}
      <div class="card shadow-sm mb-3">
        <div class="card-header border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <h3 class="card-title fw-semibold">Grafik Penjualan</h3>
            <div class="text-muted small mt-1">Omzet selama tujuh hari terakhir</div>
          </div>
          <span class="badge text-bg-light border">7 hari terakhir</span>
        </div>
        <div class="card-body pt-0">
          <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-2">
            <div>
              <div class="text-muted small">Total omzet 7 hari</div>
              <div class="fs-4 fw-semibold">Rp {{ number_format($dailySales->sum('total'), 0, ',', '.') }}</div>
            </div>
            <div class="text-muted small"><span class="d-inline-block rounded-circle bg-success me-1" style="width: 9px; height: 9px;"></span>Omzet harian</div>
          </div>

          <div class="w-100 overflow-hidden">
            <svg viewBox="0 0 780 250" role="img" aria-label="Grafik omzet tujuh hari terakhir" class="w-100" style="display:block; min-height: 190px;">
              <defs>
                <linearGradient id="salesBarGradient" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#20a66a" stop-opacity="0.95" />
                  <stop offset="100%" stop-color="#20a66a" stop-opacity="0.62" />
                </linearGradient>
              </defs>

              @foreach ($chartTicks as $tick)
                <line x1="72" y1="{{ $tick['y'] }}" x2="752" y2="{{ $tick['y'] }}" stroke="currentColor" stroke-opacity="0.10" stroke-dasharray="4 5" />
                <text x="62" y="{{ $tick['y'] + 4 }}" text-anchor="end" font-size="11" fill="currentColor" fill-opacity="0.58">{{ $tick['label'] }}</text>
              @endforeach

              <line x1="72" y1="205" x2="752" y2="205" stroke="currentColor" stroke-opacity="0.22" />

              @foreach ($chartBars as $bar)
                <rect
                  x="{{ $bar['bar_x'] }}"
                  y="{{ $bar['bar_y'] }}"
                  width="{{ $bar['bar_width'] }}"
                  height="{{ max($bar['bar_height'], $bar['total'] > 0 ? 2 : 0) }}"
                  rx="6"
                  fill="url(#salesBarGradient)"
                >
                  <title>{{ $bar['full_date'] }}: Rp {{ number_format($bar['total'], 0, ',', '.') }}</title>
                </rect>
                <text x="{{ $bar['x'] }}" y="230" text-anchor="middle" font-size="12" font-weight="600" fill="currentColor" fill-opacity="0.78">{{ $bar['label'] }}</text>
                <text x="{{ $bar['x'] }}" y="245" text-anchor="middle" font-size="10" fill="currentColor" fill-opacity="0.52">{{ \Illuminate\Support\Carbon::parse($bar['date'])->format('d/m') }}</text>
              @endforeach
            </svg>
          </div>
          <p class="text-muted small mb-0 mt-2">Tanggal tanpa transaksi tetap ditampilkan dengan omzet Rp0.</p>
        </div>
      </div>

      <div class="row g-3">
        {{-- Recent transactions --}}
        <div class="col-12 col-xl-8">
          <div class="card shadow-sm h-100 mb-0">
            <div class="card-header border-0 d-flex justify-content-between align-items-center gap-2">
              <div>
                <h3 class="card-title fw-semibold">Transaksi Terbaru</h3>
                <div class="text-muted small mt-1">Lima transaksi terakhir yang tercatat</div>
              </div>
              <a href="{{ route('history.index') }}" class="btn btn-sm btn-outline-secondary">Lihat semua</a>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-3">Invoice</th>
                      <th>Tanggal</th>
                      <th>Pelanggan</th>
                      <th class="text-end pe-3">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($recentOrders as $order)
                      <tr>
                        <td class="ps-3 fw-semibold text-nowrap">{{ $order->invoice_number ?? 'INV-' . $order->id }}</td>
                        <td class="text-nowrap">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->member?->name ?? 'Pembeli umum' }}</td>
                        <td class="text-end pe-3 text-nowrap fw-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                          <i class="bi bi-receipt fs-4 d-block mb-2"></i>
                          Belum ada transaksi yang tercatat.
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        {{-- Stock status --}}
        <div class="col-12 col-xl-4">
          <div class="card shadow-sm h-100 mb-0">
            <div class="card-header border-0 d-flex justify-content-between align-items-center gap-2">
              <div>
                <h3 class="card-title fw-semibold">Stok Habis</h3>
                <div class="text-muted small mt-1">Produk yang perlu segera diisi ulang</div>
              </div>
              <span class="badge {{ $outOfStockCount > 0 ? 'text-bg-danger' : 'text-bg-success' }}">{{ number_format($outOfStockCount, 0, ',', '.') }}</span>
            </div>
            <div class="card-body pt-0">
              @forelse ($outOfStockProducts as $product)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom gap-2">
                  <span class="text-truncate" title="{{ $product->name }}">{{ $product->name }}</span>
                  <span class="badge text-bg-danger flex-shrink-0">Habis</span>
                </div>
              @empty
                <div class="text-center text-muted py-4">
                  <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                  Tidak ada produk dengan stok habis.
                </div>
              @endforelse

              @if ($outOfStockCount > $outOfStockProducts->count())
                <div class="small text-muted mt-2">dan {{ $outOfStockCount - $outOfStockProducts->count() }} produk lainnya.</div>
              @endif
              <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary w-100 mt-3">Kelola Produk</a>
            </div>
          </div>
        </div>

        {{-- Best-selling products --}}
        <div class="col-12">
          <div class="card shadow-sm mb-0">
            <div class="card-header border-0 d-flex justify-content-between align-items-center gap-2">
              <div>
                <h3 class="card-title fw-semibold">Produk Terlaris</h3>
                <div class="text-muted small mt-1">Diurutkan berdasarkan total unit yang terjual</div>
              </div>
              <a href="{{ route('reports.products') }}" class="btn btn-sm btn-outline-secondary">Laporan Produk</a>
            </div>
            <div class="card-body">
              @forelse ($topProducts as $index => $product)
                <div class="row align-items-center g-2 {{ $loop->last ? '' : 'mb-3' }}">
                  <div class="col-1 col-md-1 text-muted small fw-semibold">{{ $index + 1 }}.</div>
                  <div class="col-5 col-md-3 text-truncate fw-medium" title="{{ $product->product_name }}">{{ $product->product_name }}</div>
                  <div class="col-4 col-md-6">
                    <div class="progress" role="progressbar" aria-label="Penjualan {{ $product->product_name }}" aria-valuenow="{{ $product->bar_width }}" aria-valuemin="0" aria-valuemax="100" style="height: 9px;">
                      <div class="progress-bar bg-success" style="width: {{ $product->bar_width }}%;"></div>
                    </div>
                  </div>
                  <div class="col-2 col-md-2 text-end small fw-semibold">{{ number_format($product->total_quantity, 0, ',', '.') }} terjual</div>
                </div>
              @empty
                <div class="text-center text-muted py-3">
                  Belum ada data penjualan produk.
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
