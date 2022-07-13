<?php

namespace BuscaAtivaEscolar\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromArray;

class RepostsExport implements FromArray, ShouldAutoSize
{
    use Exportable;
    public function __construct($childrens)
    {
        $this->childrens = $childrens;
    }
    public function array(): array
    {
        return $this->childrens;
    }
}
