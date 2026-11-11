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

            @if ($errors->any())
              <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="row">

              {{-- Kiri: daftar produk --}}
              <div class="col-lg-8 mb-3">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Pilih Produk</h3>
                  </div>
                  <div class="card-body">
                    <div class="row g-3" id="productGrid">
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
                              <button type="button" class="btn btn-primary btn-sm mt-auto add-to-cart"
                                      data-id="{{ $product->id }}"
                                      data-name="{{ $product->name }}"
                                      data-price="{{ $product->price }}"
                                      data-stock="{{ $product->stock }}"
                                      @disabled($product->stock <= 0)>
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
                <form id="checkoutForm" action="{{ route('orders.store') }}" method="POST">
                  @csrf
                  <div id="itemsInputs"></div>

                  <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Keranjang</h3>
                      <div class="card-tools">
                        <button type="button" id="clearCart" class="btn btn-outline-secondary btn-sm" disabled>
                          Kosongkan
                        </button>
                      </div>
                    </div>

                    <div class="card-body">
                      <div class="mb-3">
                        <label for="customer" class="form-label">Pelanggan</label>
                        <select id="customer" name="member_id" class="form-select">
                          <option value="">Pembeli umum</option>
                          @foreach ($members as $member)
                            <option value="{{ $member->id }}" data-phone="{{ $member->phone }}"
                                    @selected((string) old('member_id') === (string) $member->id)>
                              {{ $member->name }}
                            </option>
                          @endforeach
                        </select>
                        <div id="customer_info" class="form-text">Tanpa data member.</div>
                        @if ($members->isEmpty())
                          <div class="form-text text-warning">
                            Belum ada member aktif. Tambahkan di
                            <a href="{{ route('members.index') }}">Master Data → Member</a>.
                          </div>
                        @endif
                      </div>

                      <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                          <thead>
                            <tr>
                              <th>Produk</th>
                              <th class="text-center">Qty</th>
                              <th class="text-end">Subtotal</th>
                              <th style="width: 30px;"></th>
                            </tr>
                          </thead>
                          <tbody id="cartBody"></tbody>
                        </table>
                      </div>
                    </div>

                    <div class="card-footer">
                      <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                        <span>Total</span>
                        <span id="cartTotal">Rp 0</span>
                      </div>

                      <label for="paid_amount" class="form-label">Uang Diterima</label>
                      <div class="input-group mb-2">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="paid_amount" name="paid_amount" min="0" step="1"
                               class="form-control" value="{{ old('paid_amount') }}">
                        <button type="button" id="exactPaid" class="btn btn-outline-secondary" disabled>
                          Uang Pas
                        </button>
                      </div>

                      <div class="d-flex justify-content-between mb-3">
                        <span>Kembalian</span>
                        <strong id="cartChange">-</strong>
                      </div>

                      <button type="button" id="payButton" class="btn btn-success w-100"
                              data-bs-toggle="modal" data-bs-target="#confirmOrderModal" disabled>
                        <i class="bi bi-cash-coin"></i> Bayar
                      </button>
                    </div>
                  </div>
                </form>
              </div>

            </div>
          </div>
        </div>
        <!--end::App Content-->

{{-- Modal: Konfirmasi Transaksi --}}
<div class="modal fade" id="confirmOrderModal" tabindex="-1"
     aria-labelledby="confirmOrderModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmOrderModalLabel">Konfirmasi Transaksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <dl class="row mb-0">
          <dt class="col-6">Pelanggan</dt>
          <dd class="col-6 text-end" id="confirmCustomer">-</dd>
          <dt class="col-6">Jumlah item</dt>
          <dd class="col-6 text-end" id="confirmItems">0</dd>
          <dt class="col-6">Total</dt>
          <dd class="col-6 text-end" id="confirmTotal">Rp 0</dd>
          <dt class="col-6">Uang diterima</dt>
          <dd class="col-6 text-end" id="confirmPaid">Rp 0</dd>
          <dt class="col-6">Kembalian</dt>
          <dd class="col-6 text-end fw-bold" id="confirmChange">Rp 0</dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" form="checkoutForm" id="confirmSubmit" class="btn btn-success">
          Simpan Transaksi
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const STORAGE_KEY = 'ziemarket_cart_v1';

    @if (session('success'))
      // Transaksi berhasil disimpan: kosongkan keranjang yang tersimpan di browser.
      try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) {}
    @endif

    const fmt = new Intl.NumberFormat('id-ID');
    const rupiah = function (n) { return 'Rp ' + fmt.format(n); };

    const form = document.getElementById('checkoutForm');
    const itemsBox = document.getElementById('itemsInputs');
    const customer = document.getElementById('customer');
    const customerInfo = document.getElementById('customer_info');
    const cartBody = document.getElementById('cartBody');
    const totalEl = document.getElementById('cartTotal');
    const paidInput = document.getElementById('paid_amount');
    const changeEl = document.getElementById('cartChange');
    const payBtn = document.getElementById('payButton');
    const clearBtn = document.getElementById('clearCart');
    const exactBtn = document.getElementById('exactPaid');
    const confirmModalEl = document.getElementById('confirmOrderModal');
    const confirmSubmit = document.getElementById('confirmSubmit');

    // Pindahkan modal ke <body> agar tidak tertutup backdrop akibat stacking context layout.
    document.body.appendChild(confirmModalEl);

    // ---------- Penyimpanan keranjang ----------
    function load() {
      try {
        const raw = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '{}');
        const clean = {};
        Object.values(raw || {}).forEach(function (i) {
          if (i && Number.isInteger(i.id) && i.id > 0
              && Number.isInteger(i.qty) && i.qty > 0
              && Number.isInteger(i.stock) && Number.isFinite(i.price)
              && typeof i.name === 'string') {
            clean[i.id] = i;
          }
        });
        return clean;
      } catch (e) {
        return {};
      }
    }

    function save() {
      try { sessionStorage.setItem(STORAGE_KEY, JSON.stringify(cart)); } catch (e) {}
    }

    let cart = load();

    function total() {
      return Object.values(cart).reduce(function (sum, i) { return sum + i.price * i.qty; }, 0);
    }

    function itemCount() {
      return Object.values(cart).reduce(function (sum, i) { return sum + i.qty; }, 0);
    }

    // ---------- Aksi keranjang ----------
    function addToCart(data) {
      const id = parseInt(data.id, 10);
      const stock = parseInt(data.stock, 10);
      if (!(stock > 0)) return;

      if (cart[id]) {
        // Segarkan harga dan stok dari kartu produk yang terbaru.
        cart[id].price = Number(data.price);
        cart[id].stock = stock;
        if (cart[id].qty < stock) cart[id].qty += 1;
      } else {
        cart[id] = { id: id, name: data.name, price: Number(data.price), stock: stock, qty: 1 };
      }
      save();
      render();
    }

    function changeQty(id, delta) {
      const item = cart[id];
      if (!item) return;

      const next = item.qty + delta;
      if (next < 1) {
        delete cart[id];
      } else if (next <= item.stock) {
        item.qty = next;
      }
      save();
      render();
    }

    function removeItem(id) {
      delete cart[id];
      save();
      render();
    }

    // ---------- Tampilan ----------
    function qtyButton(label, ariaLabel, onClick, disabled) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn btn-outline-secondary btn-sm py-0 px-2';
      btn.textContent = label;
      btn.setAttribute('aria-label', ariaLabel);
      btn.disabled = disabled;
      btn.addEventListener('click', onClick);
      return btn;
    }

    function render() {
      cartBody.replaceChildren();
      const items = Object.values(cart);

      if (items.length === 0) {
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 4;
        td.className = 'text-center text-muted py-4';
        td.textContent = 'Keranjang masih kosong.';
        tr.appendChild(td);
        cartBody.appendChild(tr);
      }

      items.forEach(function (item) {
        const tr = document.createElement('tr');

        const nameTd = document.createElement('td');
        const nameEl = document.createElement('div');
        nameEl.className = 'fw-semibold';
        nameEl.textContent = item.name;
        const priceEl = document.createElement('small');
        priceEl.className = 'text-muted';
        priceEl.textContent = rupiah(item.price);
        nameTd.append(nameEl, priceEl);

        const qtyTd = document.createElement('td');
        qtyTd.className = 'text-center text-nowrap';
        const qtyEl = document.createElement('span');
        qtyEl.className = 'mx-2';
        qtyEl.textContent = item.qty;
        qtyTd.append(
          qtyButton('-', 'Kurangi jumlah', function () { changeQty(item.id, -1); }, false),
          qtyEl,
          qtyButton('+', 'Tambah jumlah', function () { changeQty(item.id, 1); }, item.qty >= item.stock)
        );

        const subTd = document.createElement('td');
        subTd.className = 'text-end';
        subTd.textContent = rupiah(item.price * item.qty);

        const delTd = document.createElement('td');
        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'btn btn-link btn-sm text-danger p-0';
        delBtn.setAttribute('aria-label', 'Hapus dari keranjang');
        const icon = document.createElement('i');
        icon.className = 'bi bi-x-lg';
        delBtn.appendChild(icon);
        delBtn.addEventListener('click', function () { removeItem(item.id); });
        delTd.appendChild(delBtn);

        tr.append(nameTd, qtyTd, subTd, delTd);
        cartBody.appendChild(tr);
      });

      totalEl.textContent = rupiah(total());
      updatePayment();
    }

    function updatePayment() {
      const t = total();
      const paid = parseInt(paidInput.value, 10);
      const hasPaid = Number.isFinite(paid) && paid >= 0;
      const change = hasPaid ? paid - t : null;

      if (change === null) {
        changeEl.textContent = '-';
      } else if (change >= 0) {
        changeEl.textContent = rupiah(change);
      } else {
        changeEl.textContent = 'Kurang ' + rupiah(-change);
      }
      changeEl.classList.toggle('text-danger', change !== null && change < 0);

      payBtn.disabled = !(t > 0 && hasPaid && paid >= t);
      clearBtn.disabled = Object.keys(cart).length === 0;
      exactBtn.disabled = t <= 0;
    }

    function updateCustomerInfo() {
      const option = customer.options[customer.selectedIndex];
      customerInfo.textContent = option.value
        ? 'No. telepon: ' + option.dataset.phone
        : 'Tanpa data member.';
    }

    // ---------- Event ----------
    document.getElementById('productGrid').addEventListener('click', function (event) {
      const button = event.target.closest('.add-to-cart');
      if (button && !button.disabled) addToCart(button.dataset);
    });

    clearBtn.addEventListener('click', function () {
      cart = {};
      save();
      render();
    });

    exactBtn.addEventListener('click', function () {
      paidInput.value = total();
      updatePayment();
    });

    paidInput.addEventListener('input', updatePayment);
    // Enter di kolom uang tidak boleh langsung mengirim form (harus lewat modal konfirmasi).
    paidInput.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') event.preventDefault();
    });

    customer.addEventListener('change', updateCustomerInfo);

    // Isi ringkasan di modal konfirmasi.
    confirmModalEl.addEventListener('show.bs.modal', function () {
      const t = total();
      const paid = parseInt(paidInput.value, 10) || 0;

      document.getElementById('confirmCustomer').textContent =
        customer.options[customer.selectedIndex].textContent.trim();
      document.getElementById('confirmItems').textContent = itemCount();
      document.getElementById('confirmTotal').textContent = rupiah(t);
      document.getElementById('confirmPaid').textContent = rupiah(paid);
      document.getElementById('confirmChange').textContent = rupiah(paid - t);
      confirmSubmit.disabled = false;
    });

    // Saat dikirim: ubah isi keranjang menjadi input tersembunyi, dan cegah klik ganda.
    form.addEventListener('submit', function (event) {
      const items = Object.values(cart);
      if (items.length === 0) {
        event.preventDefault();
        return;
      }

      itemsBox.replaceChildren();
      items.forEach(function (item, index) {
        [['product_id', item.id], ['quantity', item.qty]].forEach(function (pair) {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'items[' + index + '][' + pair[0] + ']';
          input.value = pair[1];
          itemsBox.appendChild(input);
        });
      });

      confirmSubmit.disabled = true;
    });

    updateCustomerInfo();
    render();
  });
</script>
@endsection