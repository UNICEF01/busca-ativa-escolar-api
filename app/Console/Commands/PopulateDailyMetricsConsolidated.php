<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Cassandra\Date;
use Illuminate\Console\Command;
use DB;
use Log;

class PopulateDailyMetricsConsolidated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:populate_daily_metrics_consolidated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Popular dados consolidados vindos do daily_metrics';

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
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2G');

        $this->comment('Processo de carga iniciado!');

        $tentants = $this->buscaTenants();

        foreach ($tentants as $tenant) {

            $this->comment('Processo '. $tenant->id .' | '. $tenant->name .' iniciado!');

            if($this->verificaSeExiteRematricula($tenant->id)) {
                if (!$this->verificaSeExites($tenant->id)) {
                    $dadosTenant = $this->buscarDadosTenant($tenant->id);
                    $this->inserirDados($dadosTenant);
                }
            }
            $this->comment('Processo '. $tenant->id .'finalizado!');
        }

        $this->comment('Inserção de dados consolidados finalizado!');


    }


    private function inserirDados($dadosTenant)
    {
        foreach ($dadosTenant as $dados) {
            $this->insereDados($dados);
        }
    }

    private function insereDados($dados)
    {
        $json = json_encode($dados);
        $sql = "INSERT into daily_metrics_consolidated (tenant_id, date, region,state, city, in_observation, out_of_school, cancelled, in_school, interrupted, transferred, justified_cancelled, selo, data) 
values ('$dados->tenant_id', '$dados->date', '$dados->region', '$dados->state', '$dados->city', '$dados->in_observation', '$dados->out_of_school','$dados->cancelled', '$dados->in_school', '$dados->interrupted', '$dados->transferred', '$dados->justified_cancelled', '$dados->selo','$json')";
        try {
            DB::insert($sql);
            $this->comment($dados->tenant_id . ' atualizado ');
            Log::info($dados->tenant_id . ' atualizado ');
        } catch (\Exception $e) {
            $this->comment('erro ');
            return false;
        }
    }

    private function verificaSeExites($tenantId)
    {
        $sqlTenants = "SELECT dmc.id FROM daily_metrics_consolidated dmc where dmc.tenant_id='$tenantId'";
        $response = DB::select($sqlTenants);
        if (!empty($response)) {
            return true;
        }
        return false;
    }

    private function buscarDadosTenant($tenantId)
    {
        $sql = "SELECT dm.tenant_id, dm.date, dm.city_id, dm.uf as state, c.region, c.name as city, goal,
COUNT(CASE WHEN dm.child_status = 'in_observation' THEN dm.child_status END) AS 'in_observation',
COUNT(CASE WHEN dm.child_status = 'out_of_school' THEN dm.child_status END) AS 'out_of_school',
COUNT(CASE WHEN dm.child_status = 'in_school' THEN dm.child_status END) AS 'in_school',
COUNT(CASE WHEN dm.child_status = 'interrupted' THEN dm.child_status END) AS 'interrupted',
COUNT(CASE WHEN dm.child_status = 'transferred' THEN dm.child_status END) AS 'transferred',
COUNT(CASE WHEN dm.child_status = 'cancelled' THEN dm.child_status END) AS 'cancelled',
COUNT(CASE WHEN (dm.cancel_reason <> 'duplicate' || dm.cancel_reason <> 'wrongful_insertion' || dm.cancel_reason <> 'rejected_alert') THEN dm.cancel_reason END) AS 'justified_cancelled',
(CASE WHEN g.goal IS NOT NULL THEN true ELSE false END) AS selo
FROM daily_metrics dm 
JOIN cities c ON dm.city_id = c.id
left join goals g ON c.ibge_city_id = g.id
where dm.alert_status = 'accepted' AND dm.child_status <> 'out_of_school' AND dm.tenant_id = '$tenantId'
GROUP BY dm.date, dm.city_id, dm.uf, c.region, c.name, goal";
        return DB::select($sql);

    }

    private function buscaTenants()
    {
        $sqlTenants = "SELECT t.id, t.name FROM tenants t
join case_steps_alerta csa ON csa.tenant_id = t.id
where t.is_registered = 1 and t.is_active = 1 and csa.alert_status = 'accepted' group by t.id";
        return DB::select($sqlTenants);
    }

    private function verificaSeExiteRematricula($tenantId)
    {
        $sqlTenants = "SELECT count(dm.id) as qtd FROM daily_metrics dm where (child_status = 'in_observation' OR child_status = 'in_scholl') AND tenant_id ='$tenantId'";
        $response = DB::select($sqlTenants);
        if ($response[0]->qtd > 0) {
            return true;
        }
        return false;
    }


}
