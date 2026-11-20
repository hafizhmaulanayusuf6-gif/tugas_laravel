<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Format bersama untuk semua workbook laporan.
 * Baris 1-4 adalah informasi laporan, baris 5 adalah heading tabel.
 */
abstract class StyledReportExport extends SafeExport implements
    WithEvents,
    WithColumnWidths,
    WithTitle,
    WithCustomCsvSettings,
    WithStrictNullComparison
{
    abstract protected function reportHeading(): string;

    abstract protected function reportSubtitle(): string;

    abstract protected function filterDescription(): string;

    abstract protected function lastColumn(): string;

    abstract protected function worksheetTitle(): string;

    protected function centeredColumns(): array
    {
        return ['A'];
    }

    protected function wrappedColumns(): array
    {
        return [];
    }

    protected function bodyRowHeight(): int
    {
        return 22;
    }

    public function title(): string
    {
        return $this->worksheetTitle();
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => "\r\n",
            'use_bom' => true,
            'output_encoding' => 'UTF-8',
        ];
    }

    public function registerEvents(): array
    {
        if ($this->forCsv) {
            return [];
        }

        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                // FromQuery menulis heading pada baris pertama; sisipkan ruang metadata setelah data selesai dibuat.
                $sheet->insertNewRowBefore(1, 4);

                $lastColumn = $this->lastColumn();
                $lastRow = max(5, $sheet->getHighestRow());

                // Gunakan explicit string untuk metadata agar input filter tidak dibaca sebagai formula Excel.
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->setCellValueExplicit('A1', $this->reportHeading(), DataType::TYPE_STRING);

                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->setCellValueExplicit('A2', $this->reportSubtitle(), DataType::TYPE_STRING);

                $sheet->mergeCells("A3:{$lastColumn}3");
                $sheet->setCellValueExplicit(
                    'A3',
                    'Filter: ' . $this->filterDescription(),
                    DataType::TYPE_STRING
                );

                $sheet->mergeCells("A4:{$lastColumn}4");
                $sheet->setCellValueExplicit(
                    'A4',
                    'Dibuat pada: ' . now()->format('d/m/Y H:i:s'),
                    DataType::TYPE_STRING
                );

                $sheet->setShowGridlines(false);
                $sheet->freezePane('A6');
                $sheet->setAutoFilter("A5:{$lastColumn}{$lastRow}");

                // Judul utama.
                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => [
                        'name' => 'Aptos Display',
                        'size' => 18,
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '183B32'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'indent' => 1,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(34);

                // Subjudul dan metadata filter.
                $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                    'font' => [
                        'name' => 'Aptos',
                        'size' => 10,
                        'color' => ['rgb' => '35564C'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E8F2EE'],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'indent' => 1,
                    ],
                ]);
                $sheet->getStyle("A3:{$lastColumn}4")->applyFromArray([
                    'font' => [
                        'name' => 'Aptos',
                        'size' => 9,
                        'color' => ['rgb' => '52645D'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F5F8F6'],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'indent' => 1,
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(23);
                $sheet->getRowDimension(3)->setRowHeight(21);
                $sheet->getRowDimension(4)->setRowHeight(21);

                // Header tabel.
                $sheet->getStyle("A5:{$lastColumn}5")->applyFromArray([
                    'font' => [
                        'name' => 'Aptos',
                        'size' => 10,
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '28745A'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '183B32'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(28);

                // Isi tabel dan garis pemisah tipis.
                if ($lastRow > 5) {
                    $bodyRange = "A6:{$lastColumn}{$lastRow}";
                    $sheet->getStyle($bodyRange)->applyFromArray([
                        'font' => [
                            'name' => 'Aptos',
                            'size' => 10,
                            'color' => ['rgb' => '26352F'],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_HAIR,
                                'color' => ['rgb' => 'DCE5E0'],
                            ],
                        ],
                    ]);

                    // Warna selang-seling tetap ringan dan tidak mengganggu pembacaan data.
                    $alternateRows = new Conditional();
                    $alternateRows->setConditionType(Conditional::CONDITION_EXPRESSION);
                    $alternateRows->addCondition('MOD(ROW(),2)=0');
                    $alternateRows->getStyle()->getFill()->setFillType(Fill::FILL_SOLID);
                    $alternateRows->getStyle()->getFill()->getStartColor()->setARGB('FFF3F7F5');
                    $conditionalStyles = $sheet->getStyle($bodyRange)->getConditionalStyles();
                    $conditionalStyles[] = $alternateRows;
                    $sheet->getStyle($bodyRange)->setConditionalStyles($conditionalStyles);

                    foreach ($this->centeredColumns() as $column) {
                        $sheet->getStyle("{$column}6:{$column}{$lastRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    foreach ($this->wrappedColumns() as $column) {
                        $sheet->getStyle("{$column}6:{$column}{$lastRow}")
                            ->getAlignment()
                            ->setWrapText(true);
                    }

                    for ($row = 6; $row <= $lastRow; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight($this->bodyRowHeight());
                    }
                }

                // Pengaturan cetak agar laporan tetap terbaca ketika dicetak atau disimpan sebagai PDF.
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(5, 5);
                $sheet->getPageMargins()
                    ->setTop(0.45)
                    ->setRight(0.30)
                    ->setBottom(0.45)
                    ->setLeft(0.30);
                $sheet->getHeaderFooter()->setOddFooter('&LExport laporan POS&RHalaman &P dari &N');
            },
        ];
    }
}
