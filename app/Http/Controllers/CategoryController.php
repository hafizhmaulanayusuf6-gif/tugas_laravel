<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::orderBy('name')->paginate(10);

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages(), $this->attributes());

        try {
            Category::create($validated);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Kategori gagal disimpan. Silakan coba lagi.');
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate($this->rules($category), $this->messages(), $this->attributes());

        try {
            $category->update($validated);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Kategori gagal diperbarui. Silakan coba lagi.');
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Kategori masih dipakai oleh produk dan tidak dapat dihapus.');
        }

        try {
            $category->delete();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('categories.index')
                ->with('error', 'Kategori gagal dihapus.');
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * Aturan validasi (dipakai bersama oleh store dan update).
     */
    private function rules(?Category $category = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category),
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max.string' => ':attribute maksimal :max karakter.',
            'unique' => ':attribute sudah digunakan.',
        ];
    }

    private function attributes(): array
    {
        return [
            'name' => 'Nama kategori',
        ];
    }
}