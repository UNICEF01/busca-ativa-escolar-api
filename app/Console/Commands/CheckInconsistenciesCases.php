<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;
use BuscaAtivaEscolar\Child;
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
    protected $description = 'Checa casos de inconsistencias da base de dados e salva em arquivo csv';

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

        //Consulta casos com alert_status diferentes nas tabelas Children e Alerta
        $sqlCasosComAlertStatusDiferentes = "SELECT c.id, c.created_at, c.updated_at, c.educacenso_id, c.alert_submitter_id, c.tenant_id, c.alert_status as child_alert_status, csa.alert_status as alert_alert_status, c.name
                    FROM children c
                    JOIN case_steps_alerta csa ON csa.child_id = c.id
                    where c.alert_status <> csa.alert_status";

        $casosComAlertStatusDiferentes = $this->queryObject($sqlCasosComAlertStatusDiferentes);

        //Consulta crianças sem valor no conjunto de tabelas relacionadas
        $sqlEstruturaDeCasoIncompleta = "SELECT c.id, c.created_at, c.updated_at, c.educacenso_id, c.alert_submitter_id, c.tenant_id, c.alert_status as child_alert_status, c.name 
                FROM children c WHERE 
                c.id NOT IN (SELECT child_id FROM case_steps_alerta) || 
                c.id NOT IN (SELECT child_id FROM case_steps_analise_tecnica) ||
                c.id NOT IN (SELECT child_id FROM case_steps_gestao_do_caso) || 
                c.id NOT IN (SELECT child_id FROM case_steps_observacao) ||
                c.id NOT IN (SELECT child_id FROM case_steps_pesquisa) ||
                c.id NOT IN (SELECT child_id FROM case_steps_rematricula) ||
                c.id NOT IN (SELECT child_id FROM children_cases)";

        $casosEstruturaDeCasoIncompleta = $this->queryObject($sqlEstruturaDeCasoIncompleta);


        //Consulta de criancas sem informacoes da etapa corrente - Possui step porem nao tem informacoes necessárias
        $slqCasosCriancasSemInformacaodeStep = "SELECT c.id, c.created_at, c.updated_at, c.educacenso_id, c.alert_submitter_id, c.tenant_id, c.alert_status as child_alert_status, c.name, c.current_case_id, c.current_step_id, c.current_step_type
                FROM children c 
                join case_steps_alerta csa ON csa.child_id = c.id
                join children_cases cc ON cc.child_id = c.id
                where c.current_case_id is null || c.current_step_id is null || c.current_step_type is null";

        $casosCriancasSemInformacaodeStep = $this->queryObject($slqCasosCriancasSemInformacaodeStep);


        if (($casosComAlertStatusDiferentes->total == 0) && ($casosEstruturaDeCasoIncompleta->total == 0) && ($casosCriancasSemInformacaodeStep->total == 0)) {
            $this->comment('Banco de dados consistente!');
            return;
        }

        function cleanData(&$str){
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


        if ($casosComAlertStatusDiferentes->total > 0):
            $file = fopen("/var/www/inconsistencias/alert_status_diferentes_".date('d_m_Y').".csv",'w'); 
            $flag = false;
            foreach ($casosComAlertStatusDiferentes->values as $child) {    
                if (!$flag) {
                    fputcsv($file, array_keys($child), ',', '"');
                    $flag = true;
                }
                array_walk($child, __NAMESPACE__ . '\cleanData');
                fputcsv($file, array_values($child), ',', '"');
            }
            fclose($file);
        endif;

        if ($casosEstruturaDeCasoIncompleta->total > 0):
            $file = fopen("/var/www/inconsistencias/estrutura_tabelas_incompleta_".date('d_m_Y').".csv",'w'); 
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
            $file = fopen("/var/www/inconsistencias/current_step_incompleto_".date('d_m_Y').".csv",'w'); 
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
