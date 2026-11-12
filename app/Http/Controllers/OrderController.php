<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category')->orderBy('name')->paginate(12);

        // Hanya member aktif yang bisa dipilih sebagai pelanggan.
        $members = Member::where('status', 'aktif')
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('orders.index', compact('products', 'members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['nullable', 'integer', Rule::exists('members', 'id')->where('status', 'aktif')],
            'paid_amount' => ['required', 'integer', 'min:0', 'max:9999999999'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ], $this->messages(), $this->attributes());

        // Gabungkan baris yang produknya sama: product_id => total jumlah.
        $requested = collect($validated['items'])
            ->groupBy('product_id')
            ->map(fn ($rows) => (int) $rows->sum('quantity'));

        $paid = (int) $validated['paid_amount'];

        try {
            $order = DB::transaction(function () use ($validated, $requested, $paid) {
                // Kunci baris produk agar stok tidak bisa diubah transaksi lain selagi dihitung.
                $products = Product::whereIn('id', $requested->keys())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $lines = [];
                $total = 0;

                foreach ($requested as $productId => $quantity) {
                    $product = $products->get($productId);

                    if (! $product) {
                        throw new \DomainException('Ada produk yang sudah tidak tersedia.');
                    }

                    if ($product->stock < $quantity) {
                        throw new \DomainException(
                            "Stok {$product->name} tidak cukup (tersisa {$product->stock})."
                        );
                    }

                    // Harga selalu dari database, bukan dari browser.
                    $subtotal = $product->price * $quantity;
                    $total += $subtotal;

                    $lines[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->price,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal,
                    ];
                }

                if ($paid < $total) {
                    throw new \DomainException('Uang diterima kurang dari total belanja.');
                }

                $order = Order::create([
                    'member_id' => $validated['member_id'] ?? null,
                    'total' => $total,
                    'paid_amount' => $paid,
                ]);

                $order->update([
                    'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                ]);

                $order->items()->createMany($lines);

                foreach ($requested as $productId => $quantity) {
                    $products->get($productId)->decrement('stock', $quantity);
                }

                return $order;
            });
        } catch (\DomainException $e) {
            // Kegagalan yang disengaja (stok kurang, uang kurang): semua perubahan dibatalkan.
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Transaksi gagal disimpan. Silakan coba lagi.');
        }

        $change = $order->paid_amount - $order->total;

        return redirect()
            ->route('orders.index')
            ->with('success_popup', sprintf(
                'Transaksi %s berhasil. Total Rp %s, kembalian Rp %s.',
                $order->invoice_number,
                number_format($order->total, 0, ',', '.'),
                number_format($change, 0, ',', '.')
            ));
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'items.required' => 'Keranjang masih kosong. Tambahkan produk terlebih dahulu.',
            'items.min' => 'Keranjang masih kosong. Tambahkan produk terlebih dahulu.',
            'integer' => ':attribute harus berupa angka bulat.',
            'exists' => ':attribute tidak valid.',
            'min.numeric' => ':attribute minimal :min.',
            'max.numeric' => ':attribute maksimal :max.',
        ];
    }

    private function attributes(): array
    {
        return [
            'member_id' => 'Pelanggan',
            'paid_amount' => 'Uang diterima',
            'items' => 'Keranjang',
            'items.*.product_id' => 'Produk',
            'items.*.quantity' => 'Jumlah',
        ];
    }
}