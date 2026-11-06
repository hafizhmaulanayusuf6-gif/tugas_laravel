<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(): View
    {
        $members = Member::latest('id')->paginate(10);

        return view('members.index', compact('members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages(), $this->attributes());

        try {
            Member::create($validated);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Member gagal disimpan. Silakan coba lagi.');
        }

        return redirect()
            ->route('members.index')
            ->with('success', 'Member berhasil ditambahkan.');
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $validated = $request->validate($this->rules($member), $this->messages(), $this->attributes());

        try {
            $member->update($validated);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Member gagal diperbarui. Silakan coba lagi.');
        }

        return redirect()
            ->route('members.index')
            ->with('success', 'Member berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        try {
            $member->delete();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('members.index')
                ->with('error', 'Member gagal dihapus.');
        }

        return redirect()
            ->route('members.index')
            ->with('success', 'Member berhasil dihapus.');
    }

    /**
     * Aturan validasi (dipakai bersama oleh store dan update).
     */
    private function rules(?Member $member = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('members', 'email')->ignore($member),
            ],
            'status' => ['required', Rule::in(array_keys(Member::STATUSES))],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'unique' => ':attribute sudah digunakan.',
            'in' => ':attribute tidak valid.',
            'regex' => ':attribute hanya boleh berisi angka, spasi, dan tanda + - ( ).',
            'max.string' => ':attribute maksimal :max karakter.',
        ];
    }

    private function attributes(): array
    {
        return [
            'name' => 'Nama',
            'address' => 'Alamat',
            'phone' => 'No. telepon',
            'email' => 'Email',
            'status' => 'Status',
        ];
    }
}