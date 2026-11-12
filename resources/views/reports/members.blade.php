@extends('templates.layoutApp')

@section('content')
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Members Report</h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Members Report</li>
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
            @if ($errors->any())
              <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <!--begin::Member Filters-->
            <div class="card mb-3">
              <div class="card-header">
                <h3 class="card-title">Filter Member</h3>
              </div>
              <div class="card-body">
                <form method="GET" action="{{ route('reports.members') }}" class="row g-3 align-items-end">
                  <div class="col-md-6">
                    <label for="search" class="form-label">Cari Member</label>
                    <input
                      type="search"
                      name="search"
                      id="search"
                      class="form-control"
                      placeholder="Nama, email, atau nomor telepon"
                      value="{{ $search }}"
                    >
                  </div>
                  <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                      <option value="">Semua Status</option>
                      @foreach (\App\Models\Member::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-search me-1"></i> Terapkan
                    </button>
                    <a href="{{ route('reports.members') }}" class="btn btn-outline-secondary">Reset</a>
                  </div>
                </form>
              </div>
            </div>
            <!--end::Member Filters-->

            <!--begin::Summary Cards-->
            <div class="row">
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-primary shadow-sm">
                    <i class="bi bi-people"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Total Member</span>
                    <span class="info-box-number">{{ number_format($totalMembers, 0, ',', '.') }}</span>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-success shadow-sm">
                    <i class="bi bi-person-check"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Member Aktif</span>
                    <span class="info-box-number">{{ number_format($activeMembers, 0, ',', '.') }}</span>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-secondary shadow-sm">
                    <i class="bi bi-person-dash"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Member Nonaktif</span>
                    <span class="info-box-number">{{ number_format($inactiveMembers, 0, ',', '.') }}</span>
                  </div>
                </div>
              </div>
              <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-info shadow-sm">
                    <i class="bi bi-receipt"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Pernah Bertransaksi</span>
                    <span class="info-box-number">{{ number_format($membersWithOrders, 0, ',', '.') }}</span>
                  </div>
                </div>
              </div>
            </div>
            <!--end::Summary Cards-->

            <!--begin::Member Table-->
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Daftar Member</h3>
                    <div class="card-tools text-muted small">
                      {{ number_format($members->total(), 0, ',', '.') }} member ditemukan
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-striped align-middle mb-0">
                        <thead>
                          <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Member</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th class="text-center">Transaksi</th>
                            <th class="text-end">Total Belanja</th>
                            <th>Status</th>
                            <th>Tanggal Bergabung</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($members as $member)
                            <tr>
                              <td>{{ $members->firstItem() + $loop->index }}</td>
                              <td>
                                <div class="fw-semibold">{{ $member->name }}</div>
                                <small class="text-muted">{{ str($member->address)->limit(45) }}</small>
                              </td>
                              <td>{{ $member->email }}</td>
                              <td>{{ $member->phone }}</td>
                              <td class="text-center">{{ number_format($member->orders_count, 0, ',', '.') }}</td>
                              <td class="text-end">Rp {{ number_format((float) ($member->total_spending ?? 0), 0, ',', '.') }}</td>
                              <td>
                                <span class="badge {{ $member->status === 'aktif' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                  {{ \App\Models\Member::STATUSES[$member->status] ?? ucfirst($member->status) }}
                                </span>
                              </td>
                              <td>{{ $member->created_at?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="8" class="text-center text-muted py-4">
                                @if ($search || $status)
                                  Tidak ada member yang sesuai dengan filter.
                                @else
                                  Belum ada data member.
                                @endif
                              </td>
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
            <!--end::Member Table-->
          </div>
        </div>
        <!--end::App Content-->
@endsection
