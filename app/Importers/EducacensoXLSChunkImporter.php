<?php

namespace BuscaAtivaEscolar\Importers;

use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Comment;
use BuscaAtivaEscolar\Data\AlertCause;
use BuscaAtivaEscolar\Importers\TypeImporters\ChunkEducacensoReadFilter;
use BuscaAtivaEscolar\ImportJob;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DB;

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
    public $educacenso_year = 0;

    /**
     * Handles the importing of Educacenso's XLS
     * @param ImportJob $job
     * @throws \Exception
     */
    public function handle(ImportJob $job)
    {

        $this->job = $job;
        $this->tenant = $job->tenant;
        $this->file = $job->getAbsolutePath();

        $this->agent = User::find(User::ID_EDUCACENSO_BOT);

        if (!$this->agent) {
            throw new \Exception("Failed to find Educacenso bot user!");
        }

        /** Cria o reader do PhpSpreadsheet */
        $reader = IOFactory::createReader('Xlsx');

        /**  Define a quantidade de linhas para cada chunk  **/
        $chunkSize = 100;

        /**  Instância de filtro ChunkEducacensoReadFilter **/
        $chunkFilter = new ChunkEducacensoReadFilter();

        $reader->setReadFilter($chunkFilter);

        $this->job->total_records = 0;

        /**  O limite de linha 65536 está relacionado ao número máximo de linhas de um XLS **/
        for ($startRow = 0; $startRow <= 65536; $startRow += $chunkSize) {

            $chunkFilter->setRows($startRow, $chunkSize);
            $maxRow = ($startRow + $chunkSize) - 1;
            $spreadsheet = $reader->load($this->file);
            $records = $spreadsheet->getActiveSheet()->rangeToArray('A' . $startRow . ':N' . $maxRow);

            if ($startRow > 0 and $records[0][0] == null) {
                break;
            }

            if ($startRow == 0) {

                //verifica o ano do educacenso informado no arquivo
                $textWithYear = $records[5][1];
                $patternYear = '/\b\d+\b/';
                preg_match($patternYear, $textWithYear, $matches);

                if (isset($matches[0])) {
                    $this->educacenso_year = $matches[0];
                } else {
                    throw new \Exception("Ano do Educacenso não localizado - Arquivo pode estar fora do padrão");
                }

                //verifica o cabecalho do educacenso informado no arquivo
                $headerFileEducacenso = $records[12];
                if ($this->isHeaderEducacenso($headerFileEducacenso)) {
                } else {
                    throw new \Exception("Cabeçalho padrão do Educacenso não localizado - Arquivo pode estar fora do padrão");
                }

                //verifica primeira linha de dados
                if ($records[13][0] == null) {
                    throw new \Exception("Arquivo correto, porém com primeira linha de informações vazia");
                }
            }

            if ($startRow == 0) {
                // Primeiro bloco de 100 valores. Inicia a leitura na linha 13
                DB::beginTransaction();
                try {
                    foreach ($records as $key => $record) {
                        if ($key <= 12) {
                            continue;
                        }
                        $parsedChild = $this->parseDataXlsToSystemFields($record);
                        if ($parsedChild == null) {
                            DB::commit();
                            break 2;
                        }
                        $this->insertRow($parsedChild);
                        $this->job->total_records++;
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    throw new \Exception("Erro ao inserir os dados. Tente novamente mais tarde");
                }
            } else {
                // Segundo bloco de 100 valores em diante. Inicia a leitura na linha 0
                DB::beginTransaction();
                try {
                    foreach ($records as $record) {
                        $parsedChild = $this->parseDataXlsToSystemFields($record);
                        if ($parsedChild == null) {
                            DB::commit();
                            break 2;
                        }
                        $this->insertRow($parsedChild);
                        $this->job->total_records++;
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    throw new \Exception("Erro ao inserir os dados. Tente novamente mais tarde");
                }
            }
        }

        $this->tenant->educacenso_import_details = [
            'has_imported' => true,
            'imported_at' => date('Y-m-d H:i:s'),
            'last_job_id' => $this->job->id,
            'file' => $this->file
        ];

        $this->tenant->save();
    }

    public function insertRow($data)
    {

        $data['observation'] = "Escola: " . $data['school_last_name'] . " | Modalidade de ensino: " . $data['modalidade'] . " | Etapa: " . $data['etapa'];
        $data['alert_cause_id'] = AlertCause::getBySlug('educacenso_inep')->id;
        $data['educacenso_id'] = strval($data['educacenso_id'] ?? "unkn_" . uniqid());
        $data['name'] = $data['name'];
        $data['dob'] = isset($data['dob']) ? Carbon::createFromFormat('d/m/Y', $data['dob'])->format('Y-m-d') : null;
        $data['place_uf'] = $this->tenant->city->uf;
        $data['place_city_id'] = strval($this->tenant->city->id);
        $data['place_city_name'] = $this->tenant->city->name;
        $data['place_kind'] = $data['place_kind'];
        $data['has_been_in_school'] = true;
        $data['educacenso_year'] = $this->educacenso_year;
        $data['group_id'] = $this->tenant->primary_group_id;
        $data['tree_id'] = $this->tenant->primary_group_id;


        $child = Child::spawnFromAlertData($this->tenant, $this->agent->id, $data);
        $pesquisa = Pesquisa::fetchWithinCase($child->current_case_id, Pesquisa::class, 20);
        $pesquisa->setFields($data);

        Comment::post($child, $this->agent, "Caso importado na planilha do Educacenso");
    }

    public function isThereChild($row)
    {
        $identificacao_unica = strval($row[8]);
        $child = Child::where(
            [
                ['educacenso_year', '=', $this->educacenso_year],
                ['educacenso_id', '=', $identificacao_unica],
                ['city_id', '=', $this->tenant->city_id]
            ]
        )->first();

        if ($child == null) {
            return false;
        } else {
            return true;
        }
    }

    public function isHeaderEducacenso($headerArray)
    {
        //padrão do educacenso para o ano de 2022 - confirmado pelo inep
        $headerFileEducacenso = [
            0 => 'UF',
            1 => 'Município',
            2 => 'Localização',
            3 => 'Código da escola',
            4 => 'Nome da escola',
            5 => 'Identificação única',
            6 => 'Nome do aluno',
            7 => 'Data de nascimento',
            8 => 'Filiação 1',
            9 => 'Modalidade de ensino',
            10 => 'Etapa de ensino',
            11 => NULL,
            12 => NULL,
            13 => NULL,
        ];
        return $headerArray == $headerFileEducacenso;
    }

    public function isValidLineData($arrayValues)
    {
        foreach ($arrayValues as $element) {
            if ($element == null) {
                return false;
            }
        }
        return true;
    }

    public function parseDataXlsToSystemFields($xlsData)
    {
        $data = [];

        /** Mapeamento de campos do xls para campos do sistema **/
        $fieldMap = [
            2 => 'place_kind',
            3 => 'school_last_id',
            4 => 'school_last_name',
            5 => 'educacenso_id',
            6 => 'name',
            7 => 'dob',
            8 => 'mother_name',
            9 => 'modalidade',
            10 => 'etapa'
        ];

        $placeKindMap = [
            'URBANA' => 'urban',
            'RURAL' => 'rural',
        ];

        foreach ($fieldMap as $xlsField => $systemField) {
            if (!isset($xlsData[$xlsField])) {
                return null;
            }
            $data[$systemField] = $xlsData[$xlsField];
        }

        $data['place_kind'] = $placeKindMap[$data['place_kind']];

        foreach ($fieldMap as $xlsField => $systemField) {
            if (!isset($data[$systemField]) || $data[$systemField] === null) {
                return null;
            }
        }

        return $data;
    }
}
