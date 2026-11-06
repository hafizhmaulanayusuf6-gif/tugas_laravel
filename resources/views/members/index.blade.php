@extends('templates.layoutApp')

@section('content')
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Members</h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Members</li>
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
                    <h3 class="card-title">Daftar Member</h3>
                    <div class="card-tools">
                      <button type="button" class="btn btn-primary btn-sm"
                              data-bs-toggle="modal" data-bs-target="#createMemberModal">
                        <i class="bi bi-plus-lg"></i> Tambah Member
                      </button>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-striped align-middle mb-0">
                        <thead>
                          <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>No. Telepon</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-end" style="width: 180px;">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($members as $member)
                            <tr>
                              <td>{{ $members->firstItem() + $loop->index }}</td>
                              <td>{{ $member->name }}</td>
                              <td>{{ str($member->address)->limit(50) }}</td>
                              <td>{{ $member->phone }}</td>
                              <td>{{ $member->email }}</td>
                              <td>
                                <span class="badge {{ $member->status === 'aktif' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                  {{ \App\Models\Member::STATUSES[$member->status] ?? $member->status }}
                                </span>
                              </td>
                              <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editMemberModal"
                                        data-id="{{ $member->id }}"
                                        data-name="{{ $member->name }}"
                                        data-address="{{ $member->address }}"
                                        data-phone="{{ $member->phone }}"
                                        data-email="{{ $member->email }}"
                                        data-status="{{ $member->status }}">
                                  <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <button type="button" class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#deleteMemberModal"
                                        data-id="{{ $member->id }}"
                                        data-name="{{ $member->name }}">
                                  <i class="bi bi-trash"></i> Hapus
                                </button>
                              </td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="7" class="text-center text-muted py-4">Belum ada data member.</td>
                            </tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>

                  @if ($members->total() > 0)
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                      <small class="text-muted">
                        Menampilkan {{ $members->firstItem() }}-{{ $members->lastItem() }}
                        dari {{ $members->total() }} member
                      </small>
                      {{ $members->links('pagination::bootstrap-5') }}
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--end::App Content-->

{{-- Modal: Tambah Member --}}
<div class="modal fade" id="createMemberModal" tabindex="-1"
     aria-labelledby="createMemberModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('members.store') }}" method="POST">
        @csrf
        <input type="hidden" name="form_mode" value="create">

        <div class="modal-header">
          <h5 class="modal-title" id="createMemberModalLabel">Tambah Member</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @include('members.form', ['mode' => 'create'])
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal: Edit Member --}}
<div class="modal fade" id="editMemberModal" tabindex="-1"
     aria-labelledby="editMemberModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="editMemberForm" method="POST"
            data-action-template="{{ route('members.update', ['member' => '__ID__']) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="form_mode" value="edit">
        <input type="hidden" name="edit_id" value="{{ old('edit_id') }}">

        <div class="modal-header">
          <h5 class="modal-title" id="editMemberModalLabel">Edit Member</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @include('members.form', ['mode' => 'edit'])
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
<div class="modal fade" id="deleteMemberModal" tabindex="-1"
     aria-labelledby="deleteMemberModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="deleteMemberForm" method="POST"
            data-action-template="{{ route('members.destroy', ['member' => '__ID__']) }}">
        @csrf
        @method('DELETE')

        <div class="modal-header">
          <h5 class="modal-title" id="deleteMemberModalLabel">Hapus Member</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Anda yakin ingin menghapus member <strong id="delete_name"></strong>?
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
    const createModalEl = document.getElementById('createMemberModal');
    const editModalEl = document.getElementById('editMemberModal');
    const deleteModalEl = document.getElementById('deleteMemberModal');
    const editForm = document.getElementById('editMemberForm');
    const deleteForm = document.getElementById('deleteMemberForm');

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
      document.getElementById('edit_address').value = d.address;
      document.getElementById('edit_phone').value = d.phone;
      document.getElementById('edit_email').value = d.email;
      document.getElementById('edit_status').value = d.status;
    });

    // Hapus: arahkan form dan tampilkan nama member.
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