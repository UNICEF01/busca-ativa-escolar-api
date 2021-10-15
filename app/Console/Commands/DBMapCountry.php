<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\MapCountry;
use DB;

class DBMapCountry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:map_country';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate/Update map country table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        MapCountry::truncate();
        $data = DB::table('case_steps_alerta')
            ->select(DB::raw("tenants.uf as place_uf, count(alert_status) as value"))
            ->join('tenants', 'tenants.id', '=',  'case_steps_alerta.tenant_id')
            ->where('alert_status', '=', Child::ALERT_STATUS_ACCEPTED)
            ->groupBy(['tenants.uf'])
            ->orderBy('value', 'DESC')
            ->get()->toArray();

        $data = array_map(function ($e) {
            $e->id = $this->getDataUfBySiglaUf($e->place_uf)[0];
            $e->displayValue = $e->place_uf;
            $e->showLabel = 1;
            $e->simple_name = strtolower(str_replace(" ", "", $this->getDataUfBySiglaUf($e->place_uf)[1]));
            return $e;
        }, $data);

        foreach ($data as $d) {
            //print_r($d);
            $map = new MapCountry;

            $map->place_uf = $d->place_uf;
            $map->value = $d->value;
            $map->idMap = $d->id;
            $map->displayValue = $d->displayValue;
            $map->showLabel = $d->showLabel;
            $map->simple_name = $d->simple_name;
            $map->save();
        }
    }

    protected function getDataUfBySiglaUf($nameUf)
    {
        $states = [
            'AC' => ['001', 'Acre'],
            'AL' => ['002', 'Alagoas'],
            'AP' => ['003', 'Amapa'],
            'AM' => ['004', 'Amazonas'],
            'BA' => ['005', 'Bahia'],
            'CE' => ['006', 'Ceara'],
            'DF' => ['007', 'Distrito Federal'],
            'ES' => ['008', 'Espirito Santo'],
            'GO' => ['009', 'Goias'],
            'MA' => ['010', 'Maranhao'],
            'MT' => ['011', 'Mato Grosso'],
            'MS' => ['012', 'Mato Grosso do Sul'],
            'MG' => ['013', 'Minas Gerais'],
            'PA' => ['014', 'Para'],
            'PB' => ['015', 'Paraiba'],
            'PR' => ['016', 'Parana'],
            'PE' => ['017', 'Pernambuco'],
            'PI' => ['018', 'Piaui'],
            'RJ' => ['019', 'Rio de Janeiro'],
            'RN' => ['020', 'Rio Grande do Norte'],
            'RS' => ['021', 'Rio Grande do Sul'],
            'RO' => ['022', 'Rondonia'],
            'RR' => ['023', 'Roraima'],
            'SC' => ['024', 'Santa Catarina'],
            'SP' => ['025', 'Sao Paulo'],
            'SE' => ['026', 'Sergipe'],
            'TO' => ['027', 'Tocantins']
        ];
        if ($nameUf == null) return null;
        return $states[$nameUf];
    }
}
