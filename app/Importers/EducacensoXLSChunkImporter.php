<?php
/**
 * Created by PhpStorm.
 * User: manoelfilho
 * Date: 08/10/18
 * Time: 09:26
 */

namespace BuscaAtivaEscolar\Importers;

use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Comment;
use BuscaAtivaEscolar\Data\AlertCause;
use BuscaAtivaEscolar\Importers\TypeImporters\ChunkEducacensoReadFilter;
use BuscaAtivaEscolar\Importers\TypeImporters\EducacensoImporter;
use BuscaAtivaEscolar\ImportJob;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use Config;
use Excel;
use League\CommonMark\Inline\Parser\EscapableParser;
use Log;
use PhpOffice\PhpSpreadsheet\IOFactory;


class EducacensoXLSChunkImporter
{

    const TYPE = "inep_educacenso_xls_chunck";

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
     * @var int The year of Educacenso
     * This is ony for register. The real value is registered in Children created_at.
     */
    public $educacenso_year = 2022;

    /**
     * Handles the importing of Educacenso's XLS
     * @param ImportJob $job
     * @throws \Exception
     */
    public function handle(ImportJob $job) {

        set_time_limit(0);

        $this->job = $job;
        $this->tenant = $job->tenant;
        $this->file = $job->getAbsolutePath();

        $this->agent = User::find(User::ID_EDUCACENSO_BOT);

        if(!$this->agent) {
            throw new \Exception("Failed to find Educacenso bot user!");
        }

        Log::info("[educacenso_import] Tenant {$this->tenant->name}, file {$this->file}");
        Log::info("[educacenso_import] Loading spreadsheet data into memory ...");

        /** Cria o reader do PhpSpreadsheet */
        $reader = IOFactory::createReader('Xlsx');

        /**  Define a quantidade de linhas para cada chunk  **/
        $chunkSize = 100;

        /**  Instância de filtro ChunkEducacensoReadFilter **/
        $chunkFilter = new ChunkEducacensoReadFilter();

        $reader->setReadFilter($chunkFilter);

        /**  O limite de linha 65536 está relacionado ao número máximo de linhas de um XLS **/
        for ($startRow = 0; $startRow <= 65536; $startRow += $chunkSize) {

            $chunkFilter->setRows($startRow, $chunkSize);
            $maxRow = ($startRow + $chunkSize)-1;
            $spreadsheet = $reader->load($this->file);
            $records = $spreadsheet->getActiveSheet()->rangeToArray('A'.$startRow.':N'.$maxRow);

            if($startRow > 0 AND $records[0][0] == null){
                return;
            }

            if($startRow == 0 AND $this->isHeaderEducacenso($records[12]) == false){
                throw new \Exception("Cabeçalho padrão do Educacenso não localizado");
            }

            foreach ($records as $key => $record) {

                if( ($startRow == 0 AND $key > 12) OR ($startRow > 0) ) {

                    if ($record[0] == null) { goto end; }

                    if(!$this->isThereChild($record)){

                        $this->parseChildRow($record);

                    }

                }

            }

        }

        end:
        Log::info("Última linha do arquivo localizada");

        Log::info("[educacenso_import] Completed parsing all records");

        $this->tenant->educacenso_import_details = [
            'has_imported' => true,
            'imported_at' => date('Y-m-d H:i:s'),
            'last_job_id' => $this->job->id,
            'file' => $this->file
        ];

        $this->tenant->save();

        Log::info("[educacenso_import] Job completed!");

    }

    public function parseChildRow($row) {

        Log::info("[educacenso_import] Bot Agent User: {$this->agent->id}, {$this->agent->name}");

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
            7 => 'school_last_name',
        ];

        $data = [];

        foreach($fieldMap as $xlsField => $systemField) {
            if(!isset($row[$xlsField])) continue;
            $data[$systemField] = $row[$xlsField];
        }

        $data['observation'] = "Escola: ".$row[7]." | Modalidade de ensino: ".$row[12]." | Etapa: ".$row[13];

        $data['alert_cause_id'] = AlertCause::getBySlug('educacenso_inep')->id;

        $data['educacenso_id'] = strval($data['educacenso_id'] ?? "unkn_" . uniqid());
        $data['name'] = $data['name'] ?? "-- informação não disponível --";
        $data['dob'] = isset($data['dob']) ? Carbon::createFromFormat('d/m/Y', $data['dob'])->format('Y-m-d') : null;
        $data['place_uf'] = $this->tenant->city->uf;
        $data['place_city_id'] = strval($this->tenant->city->id);
        $data['place_city_name'] = $this->tenant->city->name;
        $data['place_kind'] = isset($data['place_kind']) ? ($placeKindMap[$data['place_kind']] ?? null) : null;
        $data['has_been_in_school'] = true;
        $data['educacenso_year'] = $this->educacenso_year;

        Log::info("[educacenso_import] \t Parsed data: " . print_r($data, true));

        $child = Child::spawnFromAlertData($this->tenant, $this->agent->id, $data);

        Log::info("[educacenso_import] \t Spawned child with ID: {$child->id}");

        $pesquisa = Pesquisa::fetchWithinCase($child->current_case_id, Pesquisa::class, 20);

        Log::info("[educacenso_import] \t Found pesquisa: {$pesquisa->id}");

        $pesquisa->setFields($data);

        Log::info("[educacenso_import] \t Posting comments...");

        Comment::post($child, $this->agent, "Caso importado na planilha do Educacenso");

        if(isset($row[13])) {
            Comment::post($child, $this->agent, "Última etapa de ensino: "  . $row[13]);
        }

        Log::info("[educacenso_import] \t Child spawn complete!");

    }

    public function isThereChild($row){
        $identificacao_unica = strval($row[8]);
        $child = Child::where(
            [
                ['educacenso_year', '=', $this->educacenso_year],
                ['educacenso_id', '=', $identificacao_unica],
                ['city_id', '=', $this->tenant->city_id]
            ]
        )->first();

        if($child == null){
            return false;
        }else{
            Log::info("Child already exists ".$child->name." | ID: ".$child->id." | ID Educacenso: ".$child->educacenso_id." | Ano: ".$child->educacenso_year);
            return true;
        }
    }

    public function isHeaderEducacenso ($headerArray) {

        Log::info("CABECALHO DO ARQUIVO");
        Log::info($headerArray);
        Log::info("---------------------");

    //	COD ALUNO	NOME ALUNO	NASCIMENTO	NOME MAE	MODALIDADE	ETAPA
        $headerFileEducacenso = [
            0 => 'ESTADO',
            1 => 'MUNICIPIO',
            2 => 'DEPENDENCIA',
            3 => 'CATEGORIA',
            4 => 'CONVENIO',
            5 => 'LOCALIZACAO',
            6 => 'COD ESCOLA',
            7 => 'NOME ESCOLA',
            8 => 'COD ALUNO',
            9 => 'NOME ALUNO',
            10 => 'NASCIMENTO',
            11 => 'NOME MAE',
            12 => 'MODALIDADE',
            13 => 'ETAPA',
        ];

        return $headerArray == $headerFileEducacenso;

    }

}
