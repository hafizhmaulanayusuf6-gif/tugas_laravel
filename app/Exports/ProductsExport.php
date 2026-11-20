<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport extends StyledReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithColumnFormatting
{
    private int $rowNumber = 0;

    public function __construct(
        private Builder $query,
        private ?string $categoryName = null,
        private string $stockStatus = 'all',
        bool $forCsv = false,
        private string $search = ''
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
            'Nama Produk',
            'Kategori',
            'Harga Jual',
            'Stok',
            'Potensi Nilai Stok',
            'Status Stok',
        ];
    }

    public function map($product): array
    {
        $price = (int) $product->price;
        $stock = (int) $product->stock;

        return [
            ++$this->rowNumber,
            $product->name,
            $product->category?->name ?? '-',
            $price,
            $stock,
            $price * $stock,
            $stock > 0 ? 'Tersedia' : 'Habis',
        ];
    }

    public function columnFormats(): array
    {
        if ($this->forCsv) {
            return [];
        }

        return [
            'D' => '"Rp" #,##0;[Red]("Rp" #,##0);-',
            'E' => '#,##0;[Red](#,##0);-',
            'F' => '"Rp" #,##0;[Red]("Rp" #,##0);-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 32,
            'C' => 24,
            'D' => 19,
            'E' => 13,
            'F' => 25,
            'G' => 18,
        ];
    }

    protected function reportHeading(): string
    {
        return 'LAPORAN PRODUK';
    }

    protected function reportSubtitle(): string
    {
        return 'Rekap produk, harga jual, stok, dan potensi nilai stok (harga jual dikalikan jumlah stok).';
    }

    protected function filterDescription(): string
    {
        $category = $this->categoryName ?: 'Semua kategori';
        $stockLabel = match ($this->stockStatus) {
            'available' => 'Stok tersedia',
            'out_of_stock' => 'Stok habis',
            default => 'Semua status stok',
        };

        $description = "Kategori: {$category} | Ketersediaan: {$stockLabel}";

        if ($this->search !== '') {
            $description .= " | Pencarian: {$this->search}";
        }

        return $description;
    }

    protected function lastColumn(): string
    {
        return 'G';
    }

    protected function worksheetTitle(): string
    {
        return 'Produk';
    }

    protected function centeredColumns(): array
    {
        return ['A', 'E', 'G'];
    }
}
