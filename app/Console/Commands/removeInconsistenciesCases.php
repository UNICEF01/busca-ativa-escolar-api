<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;
use BuscaAtivaEscolar\Child;
use DB;
use Log;

class removeInconsistenciesCases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:remove_inconsistencies_cases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove casos inconsistencias da base de dados';

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
        //Consulta casos inconsistêntes
        $sqlCasosInconsistentes = "SELECT c.id
                    FROM children c
                    JOIN case_steps_alerta csa ON csa.child_id = c.id
                    where c.alert_status <> csa.alert_status";

        $casosInconsistentes = $this->queryObject($sqlCasosInconsistentes);


        //Consulta crianças sem valor no conjunto de tabelas relacionadas
        $sqlCasosCriancasSemAlerta = "SELECT c.id FROM children c WHERE 
                c.id NOT IN (SELECT child_id FROM case_steps_alerta) || 
                c.id NOT IN (SELECT child_id FROM case_steps_analise_tecnica) ||
                c.id NOT IN (SELECT child_id FROM case_steps_gestao_do_caso) || 
                c.id NOT IN (SELECT child_id FROM case_steps_observacao) ||
                c.id NOT IN (SELECT child_id FROM case_steps_pesquisa) ||
                c.id NOT IN (SELECT child_id FROM case_steps_rematricula) ||
                c.id NOT IN (SELECT child_id FROM children_cases)";

        $casosCriancasSemAlerta = $this->queryObject($sqlCasosCriancasSemAlerta);


        //Consulta crianças sem ids relacionados importantes
        $sqlCasosCriancasSemIdsRelacionados = "SELECT c.id FROM children c 
                join case_steps_alerta csa ON csa.child_id = c.id
                join children_cases cc ON cc.child_id = c.id
                where c.current_case_id is null || c.current_step_id is null || c.current_step_type is null";

        $casosCriancasSemIdsRelacionados = $this->queryObject($sqlCasosCriancasSemIdsRelacionados);


        if (($casosInconsistentes->total == 0) && ($casosCriancasSemAlerta->total == 0) && ($casosCriancasSemIdsRelacionados->total == 0)) {
            $this->comment('Banco de dados consistente!');
            return;
        }

        $resposta = $this->ask("Essa ação removera $casosCriancasSemAlerta->total crianças sem alerta, $casosInconsistentes->total  criancas inconsistencias e $casosCriancasSemIdsRelacionados->total crianças sem ids relacionados no banco de dados\n faça um backup antes pois as mudanças não poderão ser desfeitas.\n Deseja continuar? sim ou não.");

        if ($resposta == 'sim') {

            $totalCasosInconsistentes = 0;
            if ($casosInconsistentes->total > 0):
                foreach ($casosInconsistentes->values as $child) {
                    $id = $child['id'];
                    $result = $this->excluirCasos($id);
                    if ($result) {
                        $this->comment('Criança inconsistente id: ' . $id . ' excluído!');
                        Log::info('Caso inconsistente exluída id: ' . $id);
                    } else {
                        $this->comment('Criança inconsistente id: ' . $id . ' problema ao excluír!');
                        Log::info('Caso inconsistente problema ao exluir id: ' . $id);
                    }
                    $totalCasosInconsistentes++;
                }
            endif;

            $totalCasosCriancasSemAlerta = 0;
            if ($casosCriancasSemAlerta->total > 0):
                $casosCriancasSemAlertaInto = $this->queryObject($sqlCasosCriancasSemAlerta);
                foreach ($casosCriancasSemAlertaInto->values as $child) {
                    $id = $child['id'];
                    $result = $this->excluirCasos($id);
                    if ($result) {
                        $this->comment('Criança sem alerta id: ' . $id . ' excluído!');
                        Log::info('Caso sem alerta exluída id: ' . $id);
                    } else {
                        $this->comment('Criança sem alerta id: ' . $id . ' problema ao excluír!');
                        Log::info('Caso sem alerta problema ao exluir id: ' . $id);
                    }
                    $totalCasosCriancasSemAlerta++;
                }
            endif;

            $totalCasosCriancasSemIdsRelacionados = 0;
            if ($casosCriancasSemIdsRelacionados->total > 0):
                $casosCriancasSemIdsRelacionadosInto = $this->queryObject($sqlCasosCriancasSemIdsRelacionados);
                foreach ($casosCriancasSemIdsRelacionadosInto->values as $child) {
                    $id = $child['id'];
                    $result = $this->excluirCasos($id);
                    if ($result) {
                        $this->comment('Criança sem ids relacionados id: ' . $id . ' excluído!');
                        Log::info('Caso inconsistente exluída id: ' . $id);
                    } else {
                        $this->comment('Criança sem ids relacionados id: ' . $id . ' problema ao excluír!');
                        Log::info('Caso inconsistente problema ao exluir id: ' . $id);
                    }
                    $totalCasosCriancasSemIdsRelacionados++;
                }
            endif;

            $this->comment('Concluído! ' . $totalCasosCriancasSemAlerta . ' Crianças sem alerta, ' .  $totalCasosInconsistentes. ' Inconsistências ' . $totalCasosCriancasSemIdsRelacionados . ' Crianças sem ids relacionados foram excluídas do banco de dados');
            Log::info('Processo finalizado: ' . $totalCasosCriancasSemAlerta . ' Crianças sem alerta' . $totalCasosInconsistentes . ' Inconsistências' . $totalCasosCriancasSemIdsRelacionados . 'Crianças sem ids relacionados foram excluídas do banco de dados');

        } else {
            $this->comment('Ação Cancelada! ');
        }
    }

    private function excluirCasos($id)
    {
        try {
            DB::table('children')->where('id', $id)->delete();
            DB::table('children_cases')->where('child_id', $id)->delete();
            DB::table('case_steps_alerta')->where('child_id', $id)->delete();
            DB::table('case_steps_analise_tecnica')->where('child_id', $id)->delete();
            DB::table('case_steps_gestao_do_caso')->where('child_id', $id)->delete();
            DB::table('case_steps_observacao')->where('child_id', $id)->delete();
            DB::table('case_steps_pesquisa')->where('child_id', $id)->delete();
            DB::table('case_steps_rematricula')->where('child_id', $id)->delete();
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    private function queryObject($sql)
    {
        //consulta
        $queryChildrenInconsistencies = DB::select($sql);
        $resultArrayQueryChildrenInconsistencies = json_decode(json_encode($queryChildrenInconsistencies), true);

        //Contagem
        $totalCriancaInconsistentes = count($resultArrayQueryChildrenInconsistencies);

        $obj = new \stdClass();
        $obj->values = $resultArrayQueryChildrenInconsistencies;
        $obj->total = $totalCriancaInconsistentes;

        return $obj;
    }
}
