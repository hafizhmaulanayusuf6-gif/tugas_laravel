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

            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Daftar Produk</h3>
                    <div class="card-tools">
                      <button type="button" class="btn btn-primary btn-sm"
                              data-bs-toggle="modal" data-bs-target="#createProductModal">
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
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Stok</th>
                            <th class="text-end" style="width: 180px;">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($products as $product)
                            @php
                              $imageUrl = $product->image ? asset('storage/' . $product->image) : null;
                            @endphp
                            <tr>
                              <td>{{ $products->firstItem() + $loop->index }}</td>
                              <td>
                                <img
                                  src="{{ $imageUrl ?? asset('assets/assets/img/default-150x150.png') }}"
                                  alt="{{ $product->name }}"
                                  class="rounded"
                                  style="width: 60px; height: 60px; object-fit: cover;"
                                />
                              </td>
                              <td>{{ $product->name }}</td>
                              <td>{{ $product->category?->name ?? '-' }}</td>
                              <td>{{ str($product->description)->limit(60) }}</td>
                              <td class="text-end">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                              <td class="text-end">{{ number_format($product->stock, 0, ',', '.') }}</td>
                              <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editProductModal"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-description="{{ $product->description }}"
                                        data-category-id="{{ $product->category_id }}"
                                        data-price="{{ $product->price }}"
                                        data-stock="{{ $product->stock }}"
                                        data-image="{{ $imageUrl }}">
                                  <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <button type="button" class="btn btn-danger btn-sm"
                                        data-swal-delete="deleteProductForm"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}">
                                  <i class="bi bi-trash"></i> Hapus
                                </button>
                              </td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="8" class="text-center text-muted py-4">Belum ada data produk.</td>
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

{{-- Modal: Edit Produk --}}
<div class="modal fade" id="editProductModal" tabindex="-1"
     aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="editProductForm" method="POST" enctype="multipart/form-data"
            data-action-template="{{ route('products.update', ['product' => '__ID__']) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="form_mode" value="edit">
        <input type="hidden" name="edit_id" value="{{ old('edit_id') }}">

        <div class="modal-header">
          <h5 class="modal-title" id="editProductModalLabel">Edit Produk</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @include('products.form', ['mode' => 'edit'])
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Perbarui</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal: Konfirmasi Hapus --}}
<div class="modal fade" id="deleteProductModal" tabindex="-1"
     aria-labelledby="deleteProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="deleteProductForm" method="POST"
            data-action-template="{{ route('products.destroy', ['product' => '__ID__']) }}">
        @csrf
        @method('DELETE')

        <div class="modal-header">
          <h5 class="modal-title" id="deleteProductModalLabel">Hapus Produk</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Anda yakin ingin menghapus produk <strong id="delete_name"></strong>?
          Gambarnya juga akan dihapus dan tindakan ini tidak dapat dibatalkan.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Ya, Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const createModalEl = document.getElementById('createProductModal');
    const editModalEl = document.getElementById('editProductModal');
    const deleteModalEl = document.getElementById('deleteProductModal');
    const editForm = document.getElementById('editProductForm');
    const deleteForm = document.getElementById('deleteProductForm');
    const preview = document.getElementById('edit_image_preview');

    // Pindahkan semua modal ke <body> agar tidak tertutup backdrop akibat stacking context layout.
    [createModalEl, editModalEl, deleteModalEl].forEach(function (el) {
      document.body.appendChild(el);
    });

    function setEditTarget(id) {
      editForm.action = editForm.dataset.actionTemplate.replace('__ID__', id);
      editForm.querySelector('[name="edit_id"]').value = id;
    }

    // Edit: isi form dari atribut data-* pada tombol yang diklik.
    editModalEl.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      if (!button) return; // dibuka lewat script (setelah validasi gagal)

      // Bersihkan sisa error dari percobaan sebelumnya.
      editModalEl.querySelectorAll('.is-invalid').forEach(function (el) {
        el.classList.remove('is-invalid');
      });
      editModalEl.querySelectorAll('.invalid-feedback, .alert').forEach(function (el) {
        el.remove();
      });

      const d = button.dataset;
      setEditTarget(d.id);
      document.getElementById('edit_name').value = d.name;
      document.getElementById('edit_description').value = d.description;
      document.getElementById('edit_category_id').value = d.categoryId;
      document.getElementById('edit_price').value = d.price;
      document.getElementById('edit_stock').value = d.stock;
      document.getElementById('edit_image').value = ''; // input file tidak boleh diisi dari script

      if (d.image) {
        preview.src = d.image;
        preview.classList.remove('d-none');
      } else {
        preview.classList.add('d-none');
      }
    });

    // Hapus: arahkan form dan tampilkan nama produk.
    deleteModalEl.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      if (!button) return;

      deleteForm.action = deleteForm.dataset.actionTemplate.replace('__ID__', button.dataset.id);
      document.getElementById('delete_name').textContent = button.dataset.name;
    });

    @if (old('form_mode') === 'create')
      new bootstrap.Modal(createModalEl).show();
    @elseif (old('form_mode') === 'edit')
      setEditTarget(@json(old('edit_id')));
      new bootstrap.Modal(editModalEl).show();
    @endif
  });
</script>
@endsection
