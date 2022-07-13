<?php

namespace BuscaAtivaEscolar\Importers\TypeImporters;

class ChunkMunicipioReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{

    private $startRow = 0;
    private $endRow = 0;

    public function setRows($startRow, $chunkSize)
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize;
    }

    public function readCell($column, $row, $worksheetName = '')
    {
        //  Only read the heading row, and the configured rows
        if (($row == 1) || ($row >= $this->startRow && $row < $this->endRow)) {
            return true;
        }
        return false;
    }
}