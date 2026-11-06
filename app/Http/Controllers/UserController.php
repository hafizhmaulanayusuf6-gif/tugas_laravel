<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::latest('id')->paginate(10);

        return view('users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages(), $this->attributes());

        try {
            User::create([
                ...$validated,
                'password' => Hash::make($validated['password']),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'User gagal disimpan. Silakan coba lagi.');
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate($this->rules($user), $this->messages(), $this->attributes());

        // Password hanya diubah jika diisi; kosong berarti password lama dipertahankan.
        $data = Arr::except($validated, ['password']);

        if (filled($validated['password'] ?? null)) {
            $data['password'] = Hash::make($validated['password']);
        }

        try {
            $user->update($data);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'User gagal diperbarui. Silakan coba lagi.');
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        try {
            $user->delete();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('users.index')
                ->with('error', 'User gagal dihapus.');
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Aturan validasi (dipakai bersama oleh store dan update).
     */
    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'password' => $user
                ? ['nullable', 'string', 'min:8', 'max:255']
                : ['required', 'string', 'min:8', 'max:255'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'unique' => ':attribute sudah digunakan.',
            'in' => ':attribute tidak valid.',
            'min.string' => ':attribute minimal :min karakter.',
            'max.string' => ':attribute maksimal :max karakter.',
        ];
    }

    private function attributes(): array
    {
        return [
            'name' => 'User Name',
            'email' => 'Email',
            'password' => 'Password',
            'role' => 'Role',
        ];
    }
}