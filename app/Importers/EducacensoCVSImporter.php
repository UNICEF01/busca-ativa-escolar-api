<?php
/**
 * Created by PhpStorm.
 * User: manoelfilho
 * Date: 04/10/18
 * Time: 10:31
 *
 * Classe criada para possível uso de arquivos CSV. Este arquivo ainda não está sendo utilizado. Serve apenas de exemplo para possível desenvolvimento de novo processo de importação
 *
 */

namespace BuscaAtivaEscolar\Importers;

use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Comment;
use BuscaAtivaEscolar\Data\AlertCause;
use BuscaAtivaEscolar\ImportJob;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use Log;


class EducacensoCVSImporter implements Importer
{

    const TYPE = "inep_educacenso_csv";

    /**
     * @var ImportJob The import job submitted
     */
    public $job;

    /**
     * @var Tenant The tenant that is importing the alerts
     */
    public $tenant;

    /**
     * @var string The XLS file absolute path
     */
    public $file;

    /**
     * @var User The agent that is identified as the creator of the alerts
     */
    private $agent;

    /**
     * Handles the importing of Educacenso's XLS
     * @param ImportJob $job
     * @throws \Exception
     */
    public function handle(ImportJob $job) {

        $this->job = $job;
        $this->tenant = $job->tenant;
        $this->file = $job->getAbsolutePath();

        $this->agent = User::find(User::ID_EDUCACENSO_BOT);

        if(!$this->agent) {
            throw new \Exception("Failed to find Educacenso bot user!");
        }

        Log::debug("[educacenso_import] Tenant {$this->tenant->name}, file {$this->file}");

        Log::debug("[educacenso_import] Loading CSV file data into memory...");

        if (($handle = fopen($this->file, "r")) !== FALSE) {

            $numberOfAlerts = 0;
            $flag = true;

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if($flag) { $flag = false; continue; }

                $this->parseChildRow($data);

                $numberOfAlerts++;

            }
            fclose($handle);

        }

        $this->job->setTotalRecords($numberOfAlerts);

        Log::debug("[educacenso_import] Completed parsing Sheet #0!");

        $this->tenant->educacenso_import_details = [
            'has_imported' => true,
            'imported_at' => date('Y-m-d H:i:s'),
            'last_job_id' => $this->job->id,
            'file' => $this->file
        ];
        $this->tenant->save();

        Log::debug("[educacenso_import] Job completed!");

    }

    public function parseChildRow($row) {

        Log::debug("[educacenso_import] Bot Agent User: {$this->agent->id}, {$this->agent->name}");

        $placeKindMap = [
            'URBANA' => 'urban',
            'RURAL' => 'rural',
        ];

        $fieldMap = [
            8 => 'educacenso_id',
            9 => 'name',
            10 => 'dob',
            11 => 'mother_name',
            5 => 'place_kind',
            6 => 'school_last_id',
            7 => 'school_last_name'
        ];

        $data = [];

        foreach($fieldMap as $xlsField => $systemField) {
            if(!isset($row[$xlsField])) continue;
            $data[$systemField] = $row[$xlsField];
        }

        $data['alert_cause_id'] = AlertCause::getBySlug('educacenso_inep')->id;

        $data['educacenso_id'] = strval($data['educacenso_id'] ?? "unkn_" . uniqid());
        $data['name'] = $data['name'] ?? "-- informação não disponível --";
        $data['dob'] = isset($data['dob']) ? Carbon::createFromFormat('d/m/Y', $data['dob'])->format('Y-m-d') : null;
        $data['place_uf'] = $this->tenant->city->uf;
        $data['place_city_id'] = strval($this->tenant->city->id);
        $data['place_city_name'] = $this->tenant->city->name;
        $data['place_kind'] = isset($data['place_kind']) ? ($placeKindMap[$data['place_kind']] ?? null) : null;

        Log::debug("[educacenso_import] \t Parsed data: " . print_r($data, true));

        $child = Child::spawnFromAlertData($this->tenant, $this->agent->id, $data);

        Log::debug("[educacenso_import] \t Spawned child with ID: {$child->id}");

        $pesquisa = Pesquisa::fetchWithinCase($child->current_case_id, Pesquisa::class, 20);

        Log::debug("[educacenso_import] \t Found pesquisa: {$pesquisa->id}");

        $pesquisa->setFields($data);

        Log::debug("[educacenso_import] \t Posting comments...");

        Comment::post($child, $this->agent, "Caso importado na planilha do Educacenso");

        if(isset($row['Etapa de ensino'])) {
            Comment::post($child, $this->agent, "Última etapa de ensino: "  . $row['Etapa de ensino']);
        }

        Log::debug("[educacenso_import] \t Child spawn complete!");

    }

}