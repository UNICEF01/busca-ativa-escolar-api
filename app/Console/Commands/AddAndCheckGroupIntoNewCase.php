<?php
/**
 * Job para atualizar visibilidade de grupos
 */

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;
use Log;

class AddAndCheckGroupIntoNewCase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:check_news_motive_cases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Put Check in groups vitualizations in custumer';

    /**
     * @var array
     */

    private $alerts;
    /**
     * @var int
     */

    private $count = 0;

    /**
     * @var array
     */

    private $log = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->alerts = [
            10 => true, // Adolescente em conflito com a lei
            20 => true, // Criança ou adolescente com deficiência(s)
            30 => true, // Criança ou adolescente com doença(s) que impeça(m) ou dificulte(m) a frequência à escola
            40 => true, // Criança ou adolescente em abrigo
            50 => true, // Criança ou adolescente em situação de rua
            60 => true, // Criança ou adolescente vítima de abuso / violência sexual
            61 => true, // Crianças ou adolescentes migrantes estrangeiros
            70 => true, // Evasão porque sente a escola desinteressante
            80 => true, // Falta de documentação da criança ou adolescente
            90 => true, // Falta de infraestrutura escolar
            100 => true, // Falta de transporte escolar
            110 => true, // Gravidez na adolescência
            111 => true, // Infrequência escolar reportada pela gestão escolar ou pela rede de ensino
            120 => true, // Preconceito ou discriminação racial
            130 => true, // Trabalho infantil
            140 => true, // Uso, abuso ou dependência de substâncias psicoativas
            150 => true, // Violência familiar
            160 => true, // Violência na escola
            170 => true, // Mudança de domicílio, viagem ou deslocamentos frequentes
            180 => true, // Violência no território
            500 => false, // Importados do Educacenso
            600 => false, // Evasão e/ou infrequência reportada pela escola, município ou estado
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment("Processo de atualização dos grupos iniciada.");
        $results = \DB::select('SELECT * FROM groups');
        foreach ($results as $key => $values) {
            $unserialize = unserialize($values->settings);
            if (!empty($unserialize->alerts)) {
                $keyReturn = $this->checkMotiveExist($unserialize->alerts);
                if (!empty($keyReturn)) {
                    $unserialize->alerts[$keyReturn] = true;
                    $this->updateSetting($values->id, $unserialize);
                }
            }
        }
        $this->comment($this->count. ' linhas atualizadas');
        Log::info('Log Group:', $this->log);
    }

    private function checkMotiveExist($alertsBase)
    {
        foreach ($this->alerts as $key => $value) {
            if (!array_key_exists($key, $alertsBase) && $value == true) {
                return $key;
            }
        }
    }

    private function updateSetting($id, $values)
    {
        $serializeValues = addslashes(serialize($values));
        $sql = "UPDATE groups SET settings = '$serializeValues' WHERE id = '$id'";
        $update = \DB::update($sql);
        if ($update) {
            $this->comment($id . ' atualizado');
            $this->count++;
            $logId = $id;
            array_push($this->log, $logId);
        } else {
            $logId = 'fail' . $id;
            array_push($this->log, $logId);
        }
    }
}
