@extends('templates.layoutApp')

@section('content')
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Products</h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Products</li>
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

            {{-- Notifikasi --}}
            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            @if (session('error'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Daftar Produk</h3>
                    <div class="card-tools">
                      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createProductModal">
                        <i class="bi bi-plus-lg"></i> Tambah Produk
                      </button>
                    </div>
                  </div>

                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-striped align-middle mb-0">
                        <thead>
                          <tr>
                            <th style="width: 60px;">No</th>
                            <th style="width: 90px;">Gambar</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Stok</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($products as $product)
                            <tr>
                              <td>{{ $products->firstItem() + $loop->index }}</td>
                              <td>
                                <img
                                  src="{{ $product->image ? asset('storage/' . $product->image) : asset('assets/assets/img/default-150x150.png') }}"
                                  alt="{{ $product->name }}"
                                  class="rounded"
                                  style="width: 60px; height: 60px; object-fit: cover;"
                                />
                              </td>
                              <td>{{ $product->name }}</td>
                              <td>{{ str($product->description)->limit(80) }}</td>
                              <td class="text-end">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                              <td class="text-end">{{ number_format($product->stock, 0, ',', '.') }}</td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="6" class="text-center text-muted py-4">Belum ada data produk.</td>
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
              </div>
            </div>
          </div>
        </div>
        <!--end::App Content-->


{{-- Modal: Tambah Produk --}}
<div class="modal fade" id="createProductModal" tabindex="-1"
     aria-labelledby="createProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="form_mode" value="create">

        <div class="modal-header">
          <h5 class="modal-title" id="createProductModalLabel">Tambah Produk</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          @include('products.form', ['mode' => 'create'])
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const createModalEl = document.getElementById('createProductModal');

    // Pindahkan ke <body> agar tidak tertutup backdrop akibat stacking context layout.
    document.body.appendChild(createModalEl);

    @if (old('form_mode') === 'create')
      // Validasi gagal: buka lagi modal agar error dan input lama terlihat.
      new bootstrap.Modal(createModalEl).show();
    @endif
  });
</script>

@endsection