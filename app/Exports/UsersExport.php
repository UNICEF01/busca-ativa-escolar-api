<?php

namespace BuscaAtivaEscolar\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromArray, ShouldAutoSize, WithHeadings
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
            'Município',
            'Nome interno',
            'Data de adesão',
            'Data de cadastro',
            'Nome do usuário',
            'E-mail',
            'Telefone Institucional',
            'Celular Institucional',
            'Celular Pessoal',
            'Data de nascimento',
            'Tipo',
            'Grupo',
            'Instituição',
            'Posição',
            'Cadastro',
            'Data de desativação',
            'Meta Selo UNICEF'
        ];
    }
}
