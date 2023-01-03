<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;

class CheckInconsistenciesCases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:check_inconsistencies_cases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checa casos de inconsistencias da base de dados, salva em arquivo csv e permite a remocao de todas as inconsistencias encontradas';

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

        //Consulta crianca com conjunto de tabelas do caso incompletas
        $sqlEstruturaDeCasoIncompleta = "SELECT c.id, c.created_at, c.deleted_at, c.updated_at, c.educacenso_id, c.alert_submitter_id, c.tenant_id, c.alert_status as child_alert_status, c.name 
                FROM children c WHERE 
                c.id NOT IN (SELECT child_id FROM case_steps_alerta) || 
                c.id NOT IN (SELECT child_id FROM case_steps_analise_tecnica) ||
                c.id NOT IN (SELECT child_id FROM case_steps_gestao_do_caso) || 
                c.id NOT IN (SELECT child_id FROM case_steps_observacao) ||
                c.id NOT IN (SELECT child_id FROM case_steps_pesquisa) ||
                c.id NOT IN (SELECT child_id FROM case_steps_rematricula) ||
                c.id NOT IN (SELECT child_id FROM children_cases)";

        $casosEstruturaDeCasoIncompleta = $this->queryObject($sqlEstruturaDeCasoIncompleta);

        //Consulta de criancas sem informacoes da etapa corrente - Possui step porem nao tem informacoes necessárias em children_case
        $slqCasosCriancasSemInformacaodeStep = "SELECT c.id, c.created_at, c.deleted_at, c.updated_at, c.educacenso_id, c.alert_submitter_id, c.tenant_id, c.alert_status as child_alert_status, c.name, c.current_case_id, c.current_step_id, c.current_step_type
                FROM children c 
                join case_steps_alerta csa ON csa.child_id = c.id
                join children_cases cc ON cc.child_id = c.id
                where c.current_case_id is null || c.current_step_id is null || c.current_step_type is null";

        $casosCriancasSemInformacaodeStep = $this->queryObject($slqCasosCriancasSemInformacaodeStep);

        //criancas com nomes não registrados
        $sqlAlertasSemNome = "SELECT c.tenant_id, c.id, c.created_at, c.name, c.mother_name, csa.alert_status, c.alert_status, c.child_status, cc.case_status FROM children c
        join case_steps_alerta csa ON csa.child_id = c.id
        join children_cases cc ON cc.child_id = c.id
        where c.name = '-- informação não disponível --' and cc.case_status = 'in_progress'
        order by c.created_at";

        $alertasSemNome = $this->queryObject($sqlAlertasSemNome);

        if (($casosEstruturaDeCasoIncompleta->total == 0) && ($casosCriancasSemInformacaodeStep->total == 0) && ($alertasSemNome->total == 0)) {
            $this->comment('Banco de dados consistente!');
            return;
        }

        function cleanData(&$str)
        {
            if ($str == 't')
                $str = 'TRUE';
            if ($str == 'f')
                $str = 'FALSE';
            if (preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str) || preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $str)) {
                $str = "$str";
            }
            if (strstr($str, '"'))
                $str = '"' . str_replace('"', '""', $str) . '"';
        }

        if ($casosEstruturaDeCasoIncompleta->total > 0):
            $file = fopen(env('INCONSISTENCIES_FOLDER') . "/estrutura_tabelas_incompleta_" . date('d_m_Y') . ".csv", 'w');
            $flag = false;
            foreach ($casosEstruturaDeCasoIncompleta->values as $child) {
                if (!$flag) {
                    fputcsv($file, array_keys($child), ',', '"');
                    $flag = true;
                }
                array_walk($child, __NAMESPACE__ . '\cleanData');
                fputcsv($file, array_values($child), ',', '"');
            }
            fclose($file);
        endif;


        if ($casosCriancasSemInformacaodeStep->total > 0):
            $file = fopen(env('INCONSISTENCIES_FOLDER') . "/current_step_incompleto_" . date('d_m_Y') . ".csv", 'w');
            $flag = false;
            foreach ($casosCriancasSemInformacaodeStep->values as $child) {
                if (!$flag) {
                    fputcsv($file, array_keys($child), ',', '"');
                    $flag = true;
                }
                array_walk($child, __NAMESPACE__ . '\cleanData');
                fputcsv($file, array_values($child), ',', '"');
            }
            fclose($file);
        endif;

        if ($alertasSemNome->total > 0):
            $file = fopen(env('INCONSISTENCIES_FOLDER') . "/alertas_sem_nome" . date('d_m_Y') . ".csv", 'w');
            $flag = false;
            foreach ($alertasSemNome->values as $child) {
                if (!$flag) {
                    fputcsv($file, array_keys($child), ',', '"');
                    $flag = true;
                }
                array_walk($child, __NAMESPACE__ . '\cleanData');
                fputcsv($file, array_values($child), ',', '"');
            }
            fclose($file);
        endif;

        //remocoes
        $resposta = $this->ask("Essa ação removerá $casosEstruturaDeCasoIncompleta->total crianças com estrutura de tabelas incompleta, $casosCriancasSemInformacaodeStep->total crianças sem etapa em children_case e $alertasSemNome->total alertas sem o nome e informaçõe essencias da criança. \n Deseja continuar? sim ou não.");

        $totalCasosInconsistentes = 0;
        if ($casosEstruturaDeCasoIncompleta->total > 0):
            foreach ($casosEstruturaDeCasoIncompleta->values as $child) {
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

        $totalCasosInconsistentes = 0;
        if ($casosCriancasSemInformacaodeStep->total > 0):
            foreach ($casosCriancasSemInformacaodeStep->values as $child) {
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

        $totalCasosInconsistentes = 0;
        if ($alertasSemNome->total > 0):
            foreach ($alertasSemNome->values as $child) {
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
}