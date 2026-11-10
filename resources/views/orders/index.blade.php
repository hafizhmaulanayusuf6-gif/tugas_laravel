@extends('templates.layoutApp')

@section('content')
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Order (Kasir)</h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Order</li>
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
            <div class="row">

              {{-- Kiri: daftar produk --}}
              <div class="col-lg-8 mb-3">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Pilih Produk</h3>
                  </div>
                  <div class="card-body">
                    <div class="row g-3">
                      @forelse ($products as $product)
                        @php
                          $imageUrl = $product->image
                              ? asset('storage/' . $product->image)
                              : asset('assets/assets/img/default-150x150.png');
                        @endphp
                        <div class="col-6 col-md-4 col-xl-3">
                          <div class="card h-100 mb-0">
                            <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                                 class="card-img-top" style="height: 120px; object-fit: cover;">
                            <div class="card-body p-2 d-flex flex-column">
                              <div class="fw-semibold text-truncate" title="{{ $product->name }}">
                                {{ $product->name }}
                              </div>
                              <small class="text-muted">{{ $product->category?->name ?? 'Tanpa kategori' }}</small>
                              <div class="mt-1">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                              <div class="mb-2">
                                @if ($product->stock > 0)
                                  <small class="text-muted">Stok: {{ number_format($product->stock, 0, ',', '.') }}</small>
                                @else
                                  <span class="badge text-bg-danger">Habis</span>
                                @endif
                              </div>
                              <button type="button" class="btn btn-primary btn-sm mt-auto" disabled>
                                <i class="bi bi-plus-lg"></i> Tambah
                              </button>
                            </div>
                          </div>
                        </div>
                      @empty
                        <div class="col-12 text-center text-muted py-4">
                          Belum ada produk. Tambahkan dulu di
                          <a href="{{ route('products.index') }}">Master Data → Products</a>.
                        </div>
                      @endforelse
                    </div>
                  </div>

                  @if ($products->hasPages())
                    <div class="card-footer d-flex justify-content-end">
                      {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                  @endif
                </div>
              </div>

              {{-- Kanan: keranjang --}}
              <div class="col-lg-4 mb-3">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Keranjang</h3>
                  </div>
                  <div class="card-body">
                    <div class="mb-3">
                      <label for="customer" class="form-label">Pelanggan</label>
                      <select id="customer" class="form-select" disabled>
                        <option>Pembeli umum</option>
                        <option>Member</option>
                      </select>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-sm align-middle mb-0">
                        <thead>
                          <tr>
                            <th>Produk</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Subtotal</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td colspan="3" class="text-center text-muted py-4">Keranjang masih kosong.</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div class="card-footer">
                    <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                      <span>Total</span>
                      <span>Rp 0</span>
                    </div>
                    <div class="d-grid gap-2">
                      <button type="button" class="btn btn-success" disabled>
                        <i class="bi bi-cash-coin"></i> Bayar
                      </button>
                      <button type="button" class="btn btn-outline-secondary" disabled>Batal</button>
                    </div>
                    <small class="text-muted d-block mt-2">Fitur keranjang dan pembayaran belum aktif.</small>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
        <!--end::App Content-->
@endsection