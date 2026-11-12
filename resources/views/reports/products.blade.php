@extends('templates.layoutApp')

@section('content')
  <!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h1 class="mb-0 fs-3">Products Report</h1>
        </div>
        <div class="col-sm-6">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb float-sm-end">
              <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Products Report</li>
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

      <!--begin::Filters-->
      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title">Filter Produk</h3>
        </div>
        <div class="card-body">
          <form method="GET" action="{{ route('reports.products') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
              <label for="category_id" class="form-label">Kategori</label>
              <select name="category_id" id="category_id" class="form-select">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                  <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                    {{ $category->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="stock_status" class="form-label">Ketersediaan Stok</label>
              <select name="stock_status" id="stock_status" class="form-select">
                <option value="all" @selected($stockStatus === 'all')>Semua Status</option>
                <option value="available" @selected($stockStatus === 'available')>Stok Tersedia</option>
                <option value="out_of_stock" @selected($stockStatus === 'out_of_stock')>Stok Habis</option>
              </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-filter me-1"></i> Terapkan
              </button>
              <a href="{{ route('reports.products') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
          </form>
        </div>
      </div>
      <!--end::Filters-->

      <!--begin::Summary Cards-->
      <div class="row">
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-primary shadow-sm">
              <i class="bi bi-box-seam"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Jumlah Produk</span>
              <span class="info-box-number">{{ number_format($totalProducts, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-info shadow-sm">
              <i class="bi bi-boxes"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Total Stok</span>
              <span class="info-box-number">{{ number_format($totalStock, 0, ',', '.') }} <small>unit</small></span>
            </div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-danger shadow-sm">
              <i class="bi bi-exclamation-circle"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Produk Stok Habis</span>
              <span class="info-box-number">{{ number_format($outOfStockProducts, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-success shadow-sm">
              <i class="bi bi-cash-stack"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Potensi Nilai Stok</span>
              <span class="info-box-number">Rp {{ number_format((float) $potentialStockValue, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
      </div>
      <!--end::Summary Cards-->

      <!--begin::Products Table-->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Daftar Produk</h3>
              <div class="card-tools text-muted small">
                @if ($categoryId || $stockStatus !== 'all')
                  Hasil sesuai filter yang dipilih
                @else
                  Semua Produk
                @endif
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                  <thead>
                    <tr>
                      <th style="width: 60px;">No</th>
                      <th>Nama Produk</th>
                      <th>Kategori</th>
                      <th class="text-end">Harga Jual</th>
                      <th class="text-end">Stok</th>
                      <th class="text-end">Potensi Nilai Stok</th>
                      <th class="text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($products as $product)
                      <tr>
                        <td>{{ $products->firstItem() + $loop->index }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category?->name ?? '-' }}</td>
                        <td class="text-end">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($product->stock, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format((float) $product->price * (int) $product->stock, 0, ',', '.') }}</td>
                        <td class="text-center">
                          @if ($product->stock > 0)
                            <span class="badge text-bg-success">Tersedia</span>
                          @else
                            <span class="badge text-bg-danger">Habis</span>
                          @endif
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                          @if ($categoryId || $stockStatus !== 'all')
                            Tidak ada produk yang sesuai dengan filter.
                          @else
                            Belum ada data produk.
                          @endif
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            @if ($products->total() > 0)
              <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted">
                  Menampilkan {{ $products->firstItem() }}-{{ $products->lastItem() }}
                  dari {{ $products->total() }} produk
                </small>
                {{ $products->links('pagination::bootstrap-5') }}
              </div>
            @endif
          </div>
          <small class="text-muted d-block mt-2">
            Potensi nilai stok dihitung dari harga jual saat ini dikalikan jumlah stok, bukan dari harga modal.
          </small>
        </div>
      </div>
      <!--end::Products Table-->
    </div>
  </div>
  <!--end::App Content-->
@endsection

