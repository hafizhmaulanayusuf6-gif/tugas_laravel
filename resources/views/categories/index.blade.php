@extends('templates.layoutApp')

@section('content')
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Categories</h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categories</li>
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
                    <h3 class="card-title">Daftar Kategori</h3>
                    <div class="card-tools">
                      <button type="button" class="btn btn-primary btn-sm"
                              data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                        <i class="bi bi-plus-lg"></i> Tambah Kategori
                      </button>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-striped align-middle mb-0">
                        <thead>
                          <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Kategori</th>
                            <th class="text-end" style="width: 180px;">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($categories as $category)
                            <tr>
                              <td>{{ $categories->firstItem() + $loop->index }}</td>
                              <td>{{ $category->name }}</td>
                              <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                        data-id="{{ $category->id }}"
                                        data-name="{{ $category->name }}">
                                  <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <button type="button" class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#deleteCategoryModal"
                                        data-id="{{ $category->id }}"
                                        data-name="{{ $category->name }}">
                                  <i class="bi bi-trash"></i> Hapus
                                </button>
                              </td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="3" class="text-center text-muted py-4">Belum ada data kategori.</td>
                            </tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>

                  @if ($categories->total() > 0)
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                      <small class="text-muted">
                        Menampilkan {{ $categories->firstItem() }}-{{ $categories->lastItem() }}
                        dari {{ $categories->total() }} kategori
                      </small>
                      {{ $categories->links('pagination::bootstrap-5') }}
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--end::App Content-->

{{-- Modal: Tambah Kategori --}}
<div class="modal fade" id="createCategoryModal" tabindex="-1"
     aria-labelledby="createCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        <input type="hidden" name="form_mode" value="create">

        <div class="modal-header">
          <h5 class="modal-title" id="createCategoryModalLabel">Tambah Kategori</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @include('categories.form', ['mode' => 'create'])
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal: Edit Kategori --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1"
     aria-labelledby="editCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editCategoryForm" method="POST"
            data-action-template="{{ route('categories.update', ['category' => '__ID__']) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="form_mode" value="edit">
        <input type="hidden" name="edit_id" value="{{ old('edit_id') }}">

        <div class="modal-header">
          <h5 class="modal-title" id="editCategoryModalLabel">Edit Kategori</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @include('categories.form', ['mode' => 'edit'])
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
<div class="modal fade" id="deleteCategoryModal" tabindex="-1"
     aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="deleteCategoryForm" method="POST"
            data-action-template="{{ route('categories.destroy', ['category' => '__ID__']) }}">
        @csrf
        @method('DELETE')

        <div class="modal-header">
          <h5 class="modal-title" id="deleteCategoryModalLabel">Hapus Kategori</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Anda yakin ingin menghapus kategori <strong id="delete_name"></strong>?
          Tindakan ini tidak dapat dibatalkan.
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
    const createModalEl = document.getElementById('createCategoryModal');
    const editModalEl = document.getElementById('editCategoryModal');
    const deleteModalEl = document.getElementById('deleteCategoryModal');
    const editForm = document.getElementById('editCategoryForm');
    const deleteForm = document.getElementById('deleteCategoryForm');

    // Pindahkan semua modal ke <body> agar tidak tertutup backdrop akibat stacking context layout.
    [createModalEl, editModalEl, deleteModalEl].forEach(function (el) {
      document.body.appendChild(el);
    });

    // Arahkan form edit ke URL kategori yang dipilih.
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

      setEditTarget(button.dataset.id);
      document.getElementById('edit_name').value = button.dataset.name;
    });

    // Hapus: arahkan form dan tampilkan nama kategori.
    deleteModalEl.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      if (!button) return;

      deleteForm.action = deleteForm.dataset.actionTemplate.replace('__ID__', button.dataset.id);
      document.getElementById('delete_name').textContent = button.dataset.name;
    });

    @if (old('form_mode') === 'create')
      // Validasi gagal saat tambah: buka lagi modal agar error terlihat.
      new bootstrap.Modal(createModalEl).show();
    @elseif (old('form_mode') === 'edit')
      // Validasi gagal saat edit: pulihkan target form lalu buka lagi modalnya.
      setEditTarget(@json(old('edit_id')));
      new bootstrap.Modal(editModalEl).show();
    @endif
  });
</script>
@endsection