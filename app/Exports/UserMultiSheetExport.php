<?php

namespace BuscaAtivaEscolar\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class UserMultiSheetExport implements WithMultipleSheets
{
    use Exportable;

    protected $year;

    public function __construct(array $query)
    {
        $this->query = $query;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        /*for ($month = 1; $month <= count($this->query); $month++) {
            $sheets[] = new UsersExport($this->query);
        }*/
        foreach (array_chunk($this->query, 200) as $orders) {
            foreach ($orders as $order) {
                $sheets[] = new UsersExport($order);
            }
        }

        return $sheets;
    }
}
