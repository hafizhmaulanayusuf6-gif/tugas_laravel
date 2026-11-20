<?php

namespace App\Exports;

use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MembersExport extends StyledReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithColumnFormatting
{
    private int $rowNumber = 0;

    public function __construct(
        private Builder $query,
        private string $search = '',
        private ?string $status = null,
        bool $forCsv = false
    ) {
        $this->forCsv = $forCsv;
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Member',
            'Email',
            'No. Telepon',
            'Alamat',
            'Jumlah Transaksi',
            'Total Belanja',
            'Status',
            'Tanggal Bergabung',
        ];
    }

    public function map($member): array
    {
        return [
            ++$this->rowNumber,
            $member->name,
            $member->email ?? '-',
            $member->phone ?? '-',
            $member->address ?? '-',
            (int) $member->orders_count,
            (int) ($member->total_spending ?? 0),
            Member::STATUSES[$member->status] ?? ucfirst((string) $member->status),
            $this->forCsv
                ? ($member->created_at?->format('d/m/Y') ?? '-')
                : ($member->created_at ? Date::dateTimeToExcel($member->created_at) : null),
        ];
    }

    public function columnFormats(): array
    {
        if ($this->forCsv) {
            return [];
        }

        return [
            'F' => '#,##0;[Red](#,##0);-',
            'G' => '"Rp" #,##0;[Red]("Rp" #,##0);-',
            'I' => 'dd/mm/yyyy',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 26,
            'C' => 32,
            'D' => 19,
            'E' => 38,
            'F' => 19,
            'G' => 21,
            'H' => 16,
            'I' => 21,
        ];
    }

    protected function reportHeading(): string
    {
        return 'LAPORAN MEMBER';
    }

    protected function reportSubtitle(): string
    {
        return 'Daftar member beserta aktivitas transaksi dan informasi keanggotaannya.';
    }

    protected function filterDescription(): string
    {
        $searchLabel = $this->search !== '' ? 'Pencarian: ' . $this->search : 'Semua pencarian';
        $statusLabel = $this->status !== null
            ? (Member::STATUSES[$this->status] ?? $this->status)
            : 'Semua status';

        return "{$searchLabel} | Status: {$statusLabel}";
    }

    protected function lastColumn(): string
    {
        return 'I';
    }

    protected function worksheetTitle(): string
    {
        return 'Member';
    }

    protected function centeredColumns(): array
    {
        return ['A', 'F', 'H', 'I'];
    }

    protected function wrappedColumns(): array
    {
        return ['E'];
    }

    protected function bodyRowHeight(): int
    {
        return 30;
    }
}
