<?php

namespace BuscaAtivaEscolar\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromArray;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ChildrenExport implements FromArray, ShouldAutoSize, WithHeadings
{
    use Exportable, SerializesModels;
    public function __construct($childrens)
    {
        $this->childrens = $childrens;
    }
    public function array(): array
    {
        return $this->childrens;
    }
    public function headings(): array
    {
        return [
            'Nome',
            'Nome da mãe',
            'Nome do pai',
            'Risco',
            'Sexo',
            'Idade',
            'Usuário responsável',
            'Etapa',
            'Está Atrasado?',
            'Status da Criança',
            'Status do Caso',
            'Status do Prazo',
            'Status do Alerta',
            'Data Criado',
            'Data Atualizado',
            'Endereco',
            'Bairro',
            'Referencia',
            'CEP',
        ];
    }
}
