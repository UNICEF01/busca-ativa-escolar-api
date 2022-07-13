<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;

class InserirDiaAusenteGraficoRematriculas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:inserir_dia_ausente_grafico_rematriculas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotina para inserir dia ausente no gráfico de rematriculas';

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
        $dateOrigem = $this->ask("Informe a data de origem - formato yyyy-mm-dd");
        $dateDestino = $this->ask("Informe a data de destino - formato yyyy-mm-dd");

        $this->comment('Processo de inserção iniciado!');

        $sql = "SELECT * FROM daily_metrics_consolidated where date = '$dateOrigem'";
        $values = DB::select($sql);

        $this->deleteDateIfexist($dateDestino);

        foreach ($values as $value) {
            $this->insereDados($value, $dateDestino);
        }

        $this->comment('Processo de inserção Finalizado!');
    }

    private function insereDados($dados, $date)
    {
        $sql = "INSERT into daily_metrics_consolidated (tenant_id, date, region,state, city, in_observation, out_of_school, cancelled, in_school, interrupted, transferred, justified_cancelled, selo, data) 
values ('$dados->tenant_id', '$date', '$dados->region', '$dados->state', '$dados->city', '$dados->in_observation', '$dados->out_of_school','$dados->cancelled', '$dados->in_school', '$dados->interrupted', '$dados->transferred', '$dados->justified_cancelled', '$dados->selo','$dados->data')";
        try {
            DB::insert($sql);
            $this->comment($dados->tenant_id . ' inserido data: ' . $date);
            Log::info($dados->tenant_id . ' inserido data: ' . $date);
        } catch (\Exception $e) {
            $this->comment('erro ');
        }
    }

    private function deleteDateIfexist($date)
    {
        $sql = "DELETE from daily_metrics_consolidated WHERE date = '$date'";
        try {
            DB::delete($sql);
            $this->comment('data: ' . $date . ' deletada');
            Log::info( 'data: ' . $date . ' deletada');
        } catch (\Exception $e) {
            $this->comment('Erro ao deletar');
        }
    }
}
