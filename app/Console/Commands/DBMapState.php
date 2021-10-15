<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\MapState;
use DB;

class DBMapState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:map_state';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate state country table';

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
        MapState::truncate();
        $ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
        foreach ($ufs as $uf) {
            $data = DB::table('case_steps_alerta')->select(DB::raw("cities.ibge_city_id as id, count(case_steps_alerta.alert_status) as value, cities.name as name_city"))
                ->join('tenants', 'tenants.id', '=', 'case_steps_alerta.tenant_id')
                ->join('cities', 'cities.id', '=', 'tenants.city_id')

                ->where('case_steps_alerta.alert_status', '=', Child::ALERT_STATUS_ACCEPTED)
                ->where('tenants.uf', '=', $uf)
                ->groupBy(['cities.name', 'cities.ibge_city_id'])
                ->get()
                ->toArray();

            $data = array_map(
                function ($e) {
                    $e->showLabel = 0;
                    return $e;
                },
                $data
            );

            foreach ($data as $d) {
                $map = new MapState;
                $map->uf = $uf;
                $map->idMap = $d->id;
                $map->value = $d->value;
                $map->name_city = $d->name_city;
                $map->showLabel = $d->showLabel;
                $map->save();
            }
        }
    }
}
