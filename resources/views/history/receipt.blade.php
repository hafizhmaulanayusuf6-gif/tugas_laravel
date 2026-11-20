<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Struk - {{ $order->invoice_number }}</title>
  <style>
    :root {
      color-scheme: light;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 24px 16px;
      background-color: #f1f5f9;
      color: #111827;
      font-family: 'Courier New', Courier, monospace;
      font-size: 13px;
      line-height: 1.45;
    }

    .actions {
      max-width: 320px;
      margin: 0 auto 14px;
      display: flex;
      gap: 8px;
      justify-content: center;
      font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 6px;
      border: 1px solid transparent;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
    }

    .btn-primary {
      background-color: #0d6efd;
      color: #ffffff;
    }

    .btn-primary:hover {
      background-color: #0b5ed7;
    }

    .btn-secondary {
      background-color: #ffffff;
      color: #374151;
      border-color: #d1d5db;
    }

    .btn-secondary:hover {
      background-color: #f9fafb;
    }

    .receipt {
      width: 100%;
      max-width: 320px;
      margin: 0 auto;
      background-color: #ffffff;
      padding: 18px 16px;
      border-radius: 6px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .header {
      text-align: center;
      margin-bottom: 10px;
    }

    .store-name {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .store-subtitle {
      margin: 2px 0 0;
      font-size: 12px;
      color: #4b5563;
    }

    .divider {
      border: 0;
      border-top: 1px dashed #374151;
      margin: 10px 0;
    }

    .meta-row,
    .summary-row,
    .item-line {
      display: flex;
      justify-content: space-between;
      gap: 8px;
    }

    .meta-row {
      margin-bottom: 3px;
    }

    .meta-label {
      color: #4b5563;
    }

    .meta-value {
      text-align: right;
      word-break: break-word;
    }

    .item {
      margin-bottom: 7px;
    }

    .item:last-child {
      margin-bottom: 0;
    }

    .item-name {
      font-weight: 700;
      word-break: break-word;
    }

    .item-line {
      font-size: 12px;
      color: #374151;
    }

    .summary-row {
      margin-bottom: 4px;
    }

    .summary-row.total {
      font-size: 15px;
      font-weight: 700;
      margin: 6px 0;
    }

    .footer {
      text-align: center;
      margin-top: 12px;
      font-size: 11px;
      color: #4b5563;
    }

    .footer p {
      margin: 3px 0;
    }

    @media print {
      @page {
        margin: 4mm;
      }

      body {
        background-color: #ffffff;
        padding: 0;
      }

      .no-print {
        display: none !important;
      }

      .receipt {
        box-shadow: none;
        border-radius: 0;
        padding: 4px 0;
        max-width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="actions no-print">
    <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Struk</button>
    <button type="button" class="btn btn-secondary" onclick="window.close()">Tutup</button>
  </div>

  <main class="receipt">
    <header class="header">
      <h1 class="store-name">ZieMarket</h1>
      <p class="store-subtitle">Struk Pembayaran</p>
    </header>

    <hr class="divider">

    <section aria-label="Informasi Transaksi">
      <div class="meta-row">
        <span class="meta-label">No. Invoice</span>
        <span class="meta-value">{{ $order->invoice_number }}</span>
      </div>
      <div class="meta-row">
        <span class="meta-label">Tanggal</span>
        <span class="meta-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
      </div>
      <div class="meta-row">
        <span class="meta-label">Pelanggan</span>
        <span class="meta-value">{{ $order->member?->name ?? 'Pembeli umum' }}</span>
      </div>
      @if (auth()->user())
        <div class="meta-row">
          <span class="meta-label">Kasir</span>
          <span class="meta-value">{{ auth()->user()->name }}</span>
        </div>
      @endif
    </section>

    <hr class="divider">

    <section aria-label="Daftar Item">
      @foreach ($order->items as $item)
        <div class="item">
          <div class="item-name">{{ $item->product_name }}</div>
          <div class="item-line">
            <span>{{ number_format($item->quantity, 0, ',', '.') }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
          </div>
        </div>
      @endforeach
    </section>

    <hr class="divider">

    <section aria-label="Ringkasan Pembayaran">
      <div class="summary-row">
        <span>Total Item</span>
        <span>{{ number_format($order->items->sum('quantity'), 0, ',', '.') }}</span>
      </div>
      <div class="summary-row total">
        <span>TOTAL</span>
        <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
      </div>
      <div class="summary-row">
        <span>Uang Diterima</span>
        <span>Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
      </div>
      <div class="summary-row">
        <span>Kembalian</span>
        <span>Rp {{ number_format(max($order->paid_amount - $order->total, 0), 0, ',', '.') }}</span>
      </div>
    </section>

    <hr class="divider">

    <footer class="footer">
      <p><strong>Terima kasih telah berbelanja!</strong></p>
      <p>Simpan struk ini sebagai bukti pembayaran.</p>
    </footer>
  </main>

  <script>
    window.addEventListener('load', function () {
      setTimeout(function () {
        window.print();
      }, 250);
    });
  </script>
</body>
</html>
