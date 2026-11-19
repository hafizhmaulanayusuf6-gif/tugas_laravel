<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TransactionsExport extends StyledReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithColumnFormatting
{
    private int $rowNumber = 0;

    public function __construct(
        private Builder $query,
        private ?string $startDate = null,
        private ?string $endDate = null
    ) {
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Invoice',
            'Tanggal',
            'Pelanggan',
            'Item',
            'Total',
            'Uang Diterima',
            'Kembalian',
        ];
    }

    public function map($order): array
    {
        $total = (int) $order->total;
        $paid = (int) $order->paid_amount;

        return [
            ++$this->rowNumber,
            $order->invoice_number,
            Date::dateTimeToExcel($order->created_at),
            $order->member?->name ?? 'Pembeli umum',
            (int) $order->items->sum('quantity'),
            $total,
            $paid,
            max($paid - $total, 0),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => 'dd/mm/yyyy hh:mm',
            'E' => '#,##0;[Red](#,##0);-',
            'F' => '"Rp" #,##0;[Red]("Rp" #,##0);-',
            'G' => '"Rp" #,##0;[Red]("Rp" #,##0);-',
            'H' => '"Rp" #,##0;[Red]("Rp" #,##0);-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 23,
            'C' => 21,
            'D' => 27,
            'E' => 12,
            'F' => 19,
            'G' => 20,
            'H' => 19,
        ];
    }

    protected function reportHeading(): string
    {
        return 'LAPORAN TRANSAKSI';
    }

    protected function reportSubtitle(): string
    {
        return 'Rekap transaksi penjualan berdasarkan periode yang dipilih.';
    }

    protected function filterDescription(): string
    {
        if ($this->startDate === null && $this->endDate === null) {
            return 'Semua periode';
        }

        $start = $this->startDate
            ? Carbon::parse($this->startDate)->format('d/m/Y')
            : 'Tidak dibatasi';
        $end = $this->endDate
            ? Carbon::parse($this->endDate)->format('d/m/Y')
            : 'Tidak dibatasi';

        return "Periode {$start} sampai {$end}";
    }

    protected function lastColumn(): string
    {
        return 'H';
    }

    protected function worksheetTitle(): string
    {
        return 'Transaksi';
    }

    protected function centeredColumns(): array
    {
        return ['A', 'E'];
    }
}
