<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * Dasar semua export: teks selalu ditulis sebagai teks, tidak pernah sebagai rumus.
 */
abstract class SafeExport extends DefaultValueBinder implements WithCustomValueBinder
{
    protected bool $forCsv = false;

    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            if ($this->forCsv && $value !== '-' && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
                $value = "'" . $value;
            }

            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}