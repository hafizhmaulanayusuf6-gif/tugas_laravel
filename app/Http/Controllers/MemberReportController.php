<?php

namespace App\Http\Controllers;

use App\Exports\MembersExport;
use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MemberReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $this->validatedFilters($request);
        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? null;

        // Ringkasan menampilkan kondisi keseluruhan member, tidak berubah saat tabel difilter.
        $totalMembers = Member::query()->count();
        $activeMembers = Member::query()->where('status', 'aktif')->count();
        $inactiveMembers = Member::query()->where('status', 'nonaktif')->count();
        $membersWithOrders = Member::query()->has('orders')->count();

        $members = $this->membersQuery($search, $status)
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

    /** Unduh seluruh member yang cocok dengan filter, bukan hanya halaman aktif. */
    public function export(Request $request): BinaryFileResponse
    {
        $validated = $this->validatedFilters($request);
        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? null;

        $query = $this->membersQuery($search, $status)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $filename = 'laporan-member-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new MembersExport($query, $search, $status),
            $filename
        );
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(Member::STATUSES))],
        ], [
            'search.max' => 'Pencarian maksimal 100 karakter.',
            'status.in' => 'Status member tidak valid.',
        ]);
    }

    private function membersQuery(string $search, ?string $status): Builder
    {
        // Setiap member dilengkapi jumlah transaksi dan total nilai transaksinya.
        $query = Member::query()
            ->withCount('orders')
            ->withSum('orders as total_spending', 'total');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query;
    }
}
