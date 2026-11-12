<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberReportController extends Controller
{
    public function index(Request $request): View
    {
        // Validasi filter agar hanya status yang memang tersedia di model Member yang dipakai.
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(Member::STATUSES))],
        ], [
            'search.max' => 'Pencarian maksimal 100 karakter.',
            'status.in' => 'Status member tidak valid.',
        ]);

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? null;

        // Ringkasan menampilkan kondisi keseluruhan member, tidak berubah saat tabel difilter.
        $totalMembers = Member::query()->count();
        $activeMembers = Member::query()->where('status', 'aktif')->count();
        $inactiveMembers = Member::query()->where('status', 'nonaktif')->count();
        $membersWithOrders = Member::query()->has('orders')->count();

        // Setiap member dilengkapi jumlah transaksi dan total nilai transaksinya.
        $membersQuery = Member::query()
            ->withCount('orders')
            ->withSum('orders as total_spending', 'total');

        if ($search !== '') {
            $membersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status !== null) {
            $membersQuery->where('status', $status);
        }

        $members = $membersQuery
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('reports.members', compact(
            'members',
            'totalMembers',
            'activeMembers',
            'inactiveMembers',
            'membersWithOrders',
            'search',
            'status'
        ));
    }
}
