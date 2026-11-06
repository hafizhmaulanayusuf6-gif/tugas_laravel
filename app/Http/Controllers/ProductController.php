<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category')->latest('id')->paginate(10);
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('products.index', compact('products', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages(), $this->attributes());

        $path = null;

        try {
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public')
                    ?: throw new \RuntimeException('Gagal menyimpan file gambar.');
            }

            Product::create([...$validated, 'image' => $path]);
        } catch (\Throwable $e) {
            // Bersihkan file yang sudah terlanjur tersimpan agar tidak jadi file yatim.
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Produk gagal disimpan. Silakan coba lagi.');
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages(), $this->attributes());

        $oldPath = $product->image;
        $newPath = null;

        try {
            $data = Arr::except($validated, ['image']);

            if ($request->hasFile('image')) {
                $newPath = $request->file('image')->store('products', 'public')
                    ?: throw new \RuntimeException('Gagal menyimpan file gambar.');
                $data['image'] = $newPath;
            }

            $product->update($data);
        } catch (\Throwable $e) {
            // Update gagal: buang file baru, gambar lama tetap utuh.
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Produk gagal diperbarui. Silakan coba lagi.');
        }

        // Update berhasil: baru hapus gambar lama.
        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $path = $product->image;

        try {
            $product->delete();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('products.index')
                ->with('error', 'Produk gagal dihapus.');
        }

        // Record sudah terhapus: baru hapus file gambarnya.
        if ($path) {
            Storage::disk('public')->delete($path);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Aturan validasi (dipakai bersama oleh store dan update).
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:0', 'max:9999999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa angka bulat.',
            'exists' => ':attribute tidak valid.',
            'min.numeric' => ':attribute minimal :min.',
            'max.numeric' => ':attribute maksimal :max.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.file' => 'Ukuran :attribute maksimal :max KB.',
            'image' => ':attribute harus berupa file gambar.',
            'mimes' => ':attribute harus berformat: :values.',
            'uploaded' => ':attribute gagal diunggah. Pastikan ukuran file tidak melebihi batas.',
        ];
    }

    private function attributes(): array
    {
        return [
            'name' => 'Nama produk',
            'description' => 'Deskripsi',
            'category_id' => 'Kategori',
            'price' => 'Harga',
            'stock' => 'Stok',
            'image' => 'Gambar',
        ];
    }
}