@extends('templates.layoutApp')

@section('content')
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">History</h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">History</li>
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
                    <h3 class="card-title">Riwayat Transaksi</h3>
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
                            <th class="text-end" style="width: 120px;">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($orders as $order)
                            @php
                              $items = $order->items->map(fn ($item) => [
                                  'name' => $item->product_name,
                                  'price' => $item->price,
                                  'quantity' => $item->quantity,
                                  'subtotal' => $item->subtotal,
                              ])->values();
                            @endphp
                            <tr>
                              <td>{{ $orders->firstItem() + $loop->index }}</td>
                              <td>{{ $order->invoice_number }}</td>
                              <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                              <td>{{ $order->member?->name ?? 'Pembeli umum' }}</td>
                              <td class="text-center">{{ number_format($order->items->sum('quantity'), 0, ',', '.') }}</td>
                              <td class="text-end">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                              <td class="text-end">
                                <button type="button" class="btn btn-info btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#detailOrderModal"
                                        data-invoice="{{ $order->invoice_number }}"
                                        data-date="{{ $order->created_at->format('d/m/Y H:i') }}"
                                        data-customer="{{ $order->member?->name ?? 'Pembeli umum' }}"
                                        data-total="{{ $order->total }}"
                                        data-paid="{{ $order->paid_amount }}"
                                        data-items="{{ $items->toJson() }}">
                                  <i class="bi bi-eye"></i> Detail
                                </button>
                              </td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat transaksi.</td>
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
          </div>
        </div>
        <!--end::App Content-->

{{-- Modal: Detail Transaksi --}}
<div class="modal fade" id="detailOrderModal" tabindex="-1"
     aria-labelledby="detailOrderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailOrderModalLabel">Detail Transaksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <dl class="row mb-3">
          <dt class="col-sm-3">No. Invoice</dt>
          <dd class="col-sm-9" id="detail_invoice"></dd>
          <dt class="col-sm-3">Tanggal</dt>
          <dd class="col-sm-9" id="detail_date"></dd>
          <dt class="col-sm-3">Pelanggan</dt>
          <dd class="col-sm-9 mb-0" id="detail_customer"></dd>
        </dl>

        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Produk</th>
                <th class="text-end">Harga</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody id="detail_items"></tbody>
          </table>
        </div>

        <dl class="row mb-0">
          <dt class="col-6">Total</dt>
          <dd class="col-6 text-end fw-bold" id="detail_total"></dd>
          <dt class="col-6">Uang diterima</dt>
          <dd class="col-6 text-end" id="detail_paid"></dd>
          <dt class="col-6">Kembalian</dt>
          <dd class="col-6 text-end" id="detail_change"></dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('detailOrderModal');
    const itemsBody = document.getElementById('detail_items');
    const fmt = new Intl.NumberFormat('id-ID');
    const rupiah = function (n) { return 'Rp ' + fmt.format(n); };

    // Pindahkan ke <body> agar tidak tertutup backdrop akibat stacking context layout.
    document.body.appendChild(modalEl);

    modalEl.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      if (!button) return;

      const d = button.dataset;
      const total = Number(d.total);
      const paid = Number(d.paid);

      document.getElementById('detail_invoice').textContent = d.invoice;
      document.getElementById('detail_date').textContent = d.date;
      document.getElementById('detail_customer').textContent = d.customer;
      document.getElementById('detail_total').textContent = rupiah(total);
      document.getElementById('detail_paid').textContent = rupiah(paid);
      document.getElementById('detail_change').textContent = rupiah(paid - total);

      let items = [];
      try { items = JSON.parse(d.items); } catch (e) { items = []; }

      itemsBody.replaceChildren();
      items.forEach(function (item) {
        const tr = document.createElement('tr');
        [
          [item.name, ''],
          [rupiah(item.price), 'text-end'],
          [item.quantity, 'text-center'],
          [rupiah(item.subtotal), 'text-end'],
        ].forEach(function (cell) {
          const td = document.createElement('td');
          td.textContent = cell[0];
          if (cell[1]) td.className = cell[1];
          tr.appendChild(td);
        });
        itemsBody.appendChild(tr);
      });
    });
  });
</script>
@endsection