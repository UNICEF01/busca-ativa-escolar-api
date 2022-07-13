<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;

class InsertCancelReasonDailyMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'maintenance:insert_cancel_reason_daily_metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inserção do motivo de cancelamento no histórico do daily metrics';

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

        $this->comment('Processo de atualização iniciado!');

        //Consulta casos com motivos de cancelamento elegiveis de contagem
        $sqlCasos = "SELECT 
    cc.child_id, cc.cancel_reason
FROM
    children_cases cc
        LEFT JOIN
    daily_metrics dm ON dm.child_id = cc.child_id
WHERE
    dm.child_status = 'cancelled'
        AND cc.cancel_reason IS NOT NULL
        AND dm.cancel_reason IS NULL        
        AND dm.case_status = 'cancelled'
        AND (dm.step_slug = '1a_observacao'
        || dm.step_slug = '2a_observacao'
        || dm.step_slug = '3a_observacao'
        || dm.step_slug = '4a_observacao')
        AND (cc.cancel_reason <> 'wrongful_insertion'
        AND cc.cancel_reason <> 'rejected_alert'
        AND cc.cancel_reason <> 'duplicate') 
        group by cc.child_id, cc.cancel_reason;";

        $casos = DB::select($sqlCasos);

        $count = 0;

        foreach ($casos as $caso) {
            $this->atualizaCasosDailyMetrics($caso);
            $count++;

        }
        $this->comment('Processo de atualização finalizado total de atualizados: ' . $count);
    }

    private function atualizaCasosDailyMetrics($caso)
    {
        try {
            $sql = "UPDATE daily_metrics SET cancel_reason='$caso->cancel_reason' 
            WHERE case_status = 'cancelled' AND child_id='$caso->child_id' AND cancel_reason is null";
            $res = DB::update($sql);
            $this->comment('Id: ' . $caso->child_id . ' atualizado');
            Log::info('Id: ' . $caso->child_id);

        } catch (\Exception $e) {
            return false;
        }
    }
}
