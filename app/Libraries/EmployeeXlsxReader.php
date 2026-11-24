<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use SimpleXMLElement;

final class EmployeeXlsxReader extends Xlsx
{
    public const MAX_DATA_ROWS = 250;
    public const LAST_IMPORT_ROW = self::MAX_DATA_ROWS + 1;

    private bool $rowLimitExceeded = false;

    public function __construct()
    {
        parent::__construct();
        $this->setReadDataOnly(true)
            ->setReadEmptyCells(false)
            ->setIgnoreRowsWithNoCells(true)
            ->setReadFilter(new class implements IReadFilter {
                public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                {
                    return $row >= 2 && $row <= EmployeeXlsxReader::LAST_IMPORT_ROW
                        && strlen($columnAddress) === 1 && $columnAddress >= 'A' && $columnAddress <= 'F';
                }
            });
    }

    public function rowLimitExceeded(): bool
    {
        return $this->rowLimitExceeded;
    }

    protected function loadSheetData(
        ?SimpleXMLElement $xmlSheetNS,
        string $filename,
        string $dir,
        array $richData,
        Worksheet $docSheet,
        array $sharedStrings,
        array $styles,
        array $extraParameters = [],
    ): void {
        $this->rowLimitExceeded = false;
        // PhpSpreadsheet calls the read filter before it skips styled empty cells.
        if (isset($xmlSheetNS->sheetData->row)) {
            foreach ($xmlSheetNS->sheetData->row as $row) {
                foreach ($row->c as $cell) {
                    if (preg_match('/^[A-F](\d+)$/D', (string) self::getAttributes($cell)['r'], $coordinate)
                        && (int) $coordinate[1] > self::LAST_IMPORT_ROW
                        && (isset($cell->f) || (string) $cell->v !== '' || isset($cell->is))) {
                        // Return normally so the parent reader closes its ZIP handle.
                        $this->rowLimitExceeded = true;
                        return;
                    }
                }
            }
        }
        parent::loadSheetData($xmlSheetNS, $filename, $dir, $richData, $docSheet, $sharedStrings, $styles, $extraParameters);
    }
}
