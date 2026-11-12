@extends('templates.layoutApp')

@section('content')
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Users</h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
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
                    <h3 class="card-title">Daftar User</h3>
                    <div class="card-tools">
                      <button type="button" class="btn btn-primary btn-sm"
                              data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="bi bi-plus-lg"></i> Tambah User
                      </button>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-striped align-middle mb-0">
                        <thead>
                          <tr>
                            <th style="width: 60px;">No</th>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-end" style="width: 180px;">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($users as $user)
                            <tr>
                              <td>{{ $users->firstItem() + $loop->index }}</td>
                              <td>{{ $user->name }}</td>
                              <td>{{ $user->email }}</td>
                              <td>
                                @if ($user->role)
                                  <span class="badge text-bg-primary">
                                    {{ \App\Models\User::ROLES[$user->role] ?? $user->role }}
                                  </span>
                                @else
                                  -
                                @endif
                              </td>
                              <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editUserModal"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-role="{{ $user->role }}">
                                  <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <button type="button" class="btn btn-danger btn-sm"
                                        data-swal-delete="deleteUserForm"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}">
                                  <i class="bi bi-trash"></i> Hapus
                                </button>
                              </td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="5" class="text-center text-muted py-4">Belum ada data user.</td>
                            </tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>

                  @if ($users->total() > 0)
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                      <small class="text-muted">
                        Menampilkan {{ $users->firstItem() }}-{{ $users->lastItem() }}
                        dari {{ $users->total() }} user
                      </small>
                      {{ $users->links('pagination::bootstrap-5') }}
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--end::App Content-->

{{-- Modal: Tambah User --}}
<div class="modal fade" id="createUserModal" tabindex="-1"
     aria-labelledby="createUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('users.store') }}" method="POST" autocomplete="off">
        @csrf
        <input type="hidden" name="form_mode" value="create">

        <div class="modal-header">
          <h5 class="modal-title" id="createUserModalLabel">Tambah User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @include('users.form', ['mode' => 'create'])
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal: Edit User --}}
<div class="modal fade" id="editUserModal" tabindex="-1"
     aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editUserForm" method="POST" autocomplete="off"
            data-action-template="{{ route('users.update', ['user' => '__ID__']) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="form_mode" value="edit">
        <input type="hidden" name="edit_id" value="{{ old('edit_id') }}">

        <div class="modal-header">
          <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @include('users.form', ['mode' => 'edit'])
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
<div class="modal fade" id="deleteUserModal" tabindex="-1"
     aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="deleteUserForm" method="POST"
            data-action-template="{{ route('users.destroy', ['user' => '__ID__']) }}">
        @csrf
        @method('DELETE')

        <div class="modal-header">
          <h5 class="modal-title" id="deleteUserModalLabel">Hapus User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Anda yakin ingin menghapus user <strong id="delete_name"></strong>?
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
    const createModalEl = document.getElementById('createUserModal');
    const editModalEl = document.getElementById('editUserModal');
    const deleteModalEl = document.getElementById('deleteUserModal');
    const editForm = document.getElementById('editUserForm');
    const deleteForm = document.getElementById('deleteUserForm');

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
      document.getElementById('edit_email').value = d.email;
      document.getElementById('edit_password').value = ''; // password tidak pernah ditampilkan
      document.getElementById('edit_role').value = d.role;
    });

    // Hapus: arahkan form dan tampilkan nama user.
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
