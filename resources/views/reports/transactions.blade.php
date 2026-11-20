@extends('templates.layoutApp')

@section('content')
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Transactions Report</h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Transactions Report</li>
                  </ol>
                </nav>
              </div>
            </div>
          </div>
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <div class="container-fluid">

            <!--begin::Date Filter-->
            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title">Filter Periode Transaksi</h3>
              </div>
              <div class="card-body">
                <form method="GET" action="{{ route('reports.transactions') }}" class="row g-3 align-items-end">
                  @if ($search !== '')
                    <input type="hidden" name="search" value="{{ $search }}">
                  @endif
                  <div class="col-md-6 col-xl">
                    <label for="start_date" class="form-label">Dari Tanggal</label>
                    <input
                      type="date"
                      name="start_date"
                      id="start_date"
                      class="form-control"
                      value="{{ $startDate }}"
                    >
                  </div>
                  <div class="col-md-6 col-xl">
                    <label for="end_date" class="form-label">Sampai Tanggal</label>
                    <input
                      type="date"
                      name="end_date"
                      id="end_date"
                      class="form-control"
                      value="{{ $endDate }}"
                    >
                  </div>
                  <div class="col-12 col-xl-auto d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-filter me-1"></i> Terapkan
                    </button>
                    <a href="{{ route('reports.transactions') }}" class="btn btn-outline-secondary">
                      Reset
                    </a>
                    <a href="{{ route('reports.transactions.export', request()->only('start_date', 'end_date', 'search')) }}"
                       class="btn btn-success">
                      <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                    </a>
                    <a href="{{ route('reports.transactions.export.csv', request()->only('start_date', 'end_date', 'search')) }}"
                       class="btn btn-outline-success">
                      <i class="bi bi-filetype-csv me-1"></i> Export CSV
                    </a>
                  </div>
                </form>
              </div>
            </div>
            <!--end::Date Filter-->

            <!--begin::Summary Cards-->
            <div class="row">
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-primary shadow-sm">
                    <i class="bi bi-receipt"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Total Transaksi</span>
                    <span class="info-box-number">{{ number_format($totalTransactions, 0, ',', '.') }}</span>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-success shadow-sm">
                    <i class="bi bi-cash-stack"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Total Penjualan</span>
                    <span class="info-box-number">Rp {{ number_format($totalSales, 0, ',', '.') }}</span>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-info shadow-sm">
                    <i class="bi bi-box-seam"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Item Terjual</span>
                    <span class="info-box-number">{{ number_format($totalItemsSold, 0, ',', '.') }}</span>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-warning shadow-sm">
                    <i class="bi bi-calculator"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Rata-rata Transaksi</span>
                    <span class="info-box-number">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</span>
                  </div>
                </div>
              </div>
            </div>
            <!--end::Summary Cards-->

            <!--begin::Transaction Table-->
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Daftar Transaksi</h3>
                    <div class="card-tools d-flex flex-wrap align-items-center gap-2">
                      <span class="text-muted small">
                        @if ($startDate || $endDate)
                          Periode:
                          {{ $startDate ? \Illuminate\Support\Carbon::parse($startDate)->format('d/m/Y') : 'Awal' }}
                          &ndash;
                          {{ $endDate ? \Illuminate\Support\Carbon::parse($endDate)->format('d/m/Y') : 'Sekarang' }}
                        @else
                          Semua Periode
                        @endif
                      </span>
                      <form method="GET" action="{{ route('reports.transactions') }}" class="d-flex align-items-center">
                        @if ($startDate)
                          <input type="hidden" name="start_date" value="{{ $startDate }}">
                        @endif
                        @if ($endDate)
                          <input type="hidden" name="end_date" value="{{ $endDate }}">
                        @endif
                        <div class="input-group input-group-sm" style="width: 240px;">
                          <input
                            type="search"
                            name="search"
                            class="form-control"
                            placeholder="Cari invoice atau pelanggan..."
                            value="{{ $search }}"
                            aria-label="Cari transaksi"
                          >
                          <button type="submit" class="btn btn-outline-secondary" title="Cari">
                            <i class="bi bi-search"></i>
                          </button>
                          @if ($search !== '')
                            <a href="{{ route('reports.transactions', request()->only('start_date', 'end_date')) }}"
                               class="btn btn-outline-secondary" title="Reset pencarian">
                              <i class="bi bi-x-lg"></i>
                            </a>
                          @endif
                        </div>
                      </form>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-striped align-middle mb-0">
                        <thead>
                          <tr>
                            <th style="width: 60px;">No</th>
                            <th>No. Invoice</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th class="text-center">Item</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Uang Diterima</th>
                            <th class="text-end">Kembalian</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($orders as $order)
                            <tr>
                              <td>{{ $orders->firstItem() + $loop->index }}</td>
                              <td>{{ $order->invoice_number }}</td>
                              <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                              <td>{{ $order->member?->name ?? 'Pembeli umum' }}</td>
                              <td class="text-center">{{ number_format($order->items->sum('quantity'), 0, ',', '.') }}</td>
                              <td class="text-end">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                              <td class="text-end">Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</td>
                              <td class="text-end">Rp {{ number_format(max($order->paid_amount - $order->total, 0), 0, ',', '.') }}</td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="8" class="text-center text-muted py-4">
                                @if ($search !== '' || $startDate || $endDate)
                                  Tidak ada transaksi yang sesuai dengan filter atau pencarian.
                                @else
                                  Belum ada data transaksi.
                                @endif
                              </td>
                            </tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>

                  @if ($orders->total() > 0)
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                      <small class="text-muted">
                        Menampilkan {{ $orders->firstItem() }}-{{ $orders->lastItem() }}
                        dari {{ $orders->total() }} transaksi
                      </small>
                      {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
                  @endif
                </div>
              </div>
            </div>
            <!--end::Transaction Table-->
          </div>
        </div>
        <!--end::App Content-->
@endsection

