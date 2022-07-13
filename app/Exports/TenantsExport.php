<?php

namespace BuscaAtivaEscolar\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TenantsExport implements FromArray, ShouldAutoSize, WithHeadings
{
    use Exportable;
    public function __construct($tenants)
    {
        $this->tenants = $tenants;
    }
    public function array(): array
    {
        return $this->tenants;
    }
    public function headings(): array
    {
        return [
            'Nome',
            'UF',
            'Está ativo?',
            'Data da última atividade',
            'Tempo',
            'Está configurado?',
            'Data de cadastro',
            'Data de ativação',
            'Data de exclusão'
        ];
    }
}
