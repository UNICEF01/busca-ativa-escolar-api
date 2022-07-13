<?php

namespace BuscaAtivaEscolar\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StateExport implements FromArray, ShouldAutoSize, WithHeadings
{
    use Exportable;

    public function __construct($query)
    {
        $this->query = $query;
    }
    public function array(): array
    {
        return $this->query;
    }
    public function headings(): array
    {
        return [
            'UF',
            'Data de adesão',
            'Data exclusão',
            'Gestor estadual',
            'Gestor estadual - CPF',
            'Gestor estadual - Data de nascimento',
            'Gestor estadual - Email',
            'Gestor estadual - Telefone',
            'Gestor estadual - Função',
            'Gestor estadual - Instituição',
            'Coordenador estadual',
            'Coordenador estadual - CPF',
            'Coordenador estadual - Data de nascimento',
            'Coordenador estadual - Email',
            'Coordenador estadual - Telefone',
            'Coordenador estadual - Função',
            'Coordenador estadual -Instituição',
        ];
    }
}
