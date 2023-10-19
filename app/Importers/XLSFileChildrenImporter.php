<?php

namespace BuscaAtivaEscolar\Importers;

use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Comment;
use BuscaAtivaEscolar\Data\AlertCause;
use BuscaAtivaEscolar\Importers\TypeImporters\ChunkMunicipioReadFilter;
use BuscaAtivaEscolar\ImportJob;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

class XLSFileChildrenImporter implements Importer
{
    const TYPE = "xls_file_children";

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
     * @var int The total records imported from xls file
     */
    private $total_records;

    /**
     * @var array with objects
     */
    private $duplicateds = [];

    public function handle(ImportJob $job)
    {

        set_time_limit(0);

        $this->job = $job;
        $this->total_records = 0;
        $this->tenant = $job->tenant;
        $this->file = $job->getAbsolutePath();

        $this->agent = User::find(User::ID_IMPORT_XLS_BOT);

        if (!$this->agent) {
            throw new Exception("Failed to find Educacenso bot user!");
        }

        /** Cria o reader do PhpSpreadsheet */
        $reader = IOFactory::createReader('Xlsx');

        /**  Define a quantidade de linhas para cada chunk  **/
        $chunkSize = 100;

        /**  Instância de filtro ChunkEducacensoReadFilter **/
        $chunkFilter = new ChunkMunicipioReadFilter();

        $reader->setReadFilter($chunkFilter);

        /** VALIDACAO DO ARQUIVO -  O limite de linha 65536 está relacionado ao número máximo de linhas de um XLS **/
        for ($startRow = 0; $startRow <= 65536; $startRow += $chunkSize) {

            $chunkFilter->setRows($startRow, $chunkSize);
            $maxRow = ($startRow + $chunkSize) - 1;
            $spreadsheet = $reader->load($this->file);
            $records = $spreadsheet->getActiveSheet()->rangeToArray('A' . $startRow . ':K' . $maxRow);

            //validacao do cabecalho
            if ($startRow == 0 and $this->isHeaderPatterFileMunicipio($records[1]) == false) {
                throw new \Exception("Cabeçalho padrão da importação não localizado");
            }

            //Validacao de cada bloco do chunk por linha
            foreach ($records as $key => $record) {
                if (($startRow == 0 and $key > 1) or ($startRow > 0)) {
                    if ($record[0] == null) {
                        goto lastLineValidation;
                    }
                    $this->validateRow($record);
                }
            }
        }

        lastLineValidation:

        /** IMPORTACAO DOS DADOS**/
        for ($startRow = 0; $startRow <= 65536; $startRow += $chunkSize) {

            $chunkFilter->setRows($startRow, $chunkSize);
            $maxRow = ($startRow + $chunkSize) - 1;
            $spreadsheet = $reader->load($this->file);
            $records = $spreadsheet->getActiveSheet()->rangeToArray('A' . $startRow . ':K' . $maxRow);

            foreach ($records as $key => $record) {
                if (($startRow == 0 and $key > 1) or ($startRow > 0)) {

                    if ($record[0] == null) {
                        goto end;
                    }

                    if ($this->isThereChild($record) == false) {
                        $this->parseChild($record);
                    }
                }
            }
        }

        end:

        $job->setTotalRecords($this->total_records);
        $job->setDuplicateds($this->duplicateds);
    }

    public function validateRow($row)
    {

        //Validacoes dos campos obrigatórios
        if ($row[0] == null) {
            throw new Exception("Arquivo inválido. Nome da criança não informado");
        }

        if ($row[2] == null) {
            throw new Exception("Arquivo inválido. Nome da mãe ou responsável pela criança não informado");
        }

        if ($row[6] == null) {
            throw new Exception("Arquivo inválido. Estado não informado");
        }

        if ($row[7] == null) {
            throw new Exception("Arquivo inválido. Cidade não informada");
        }

        if ($row[5] == null) {
            throw new Exception("Arquivo inválido. Bairro não informado");
        }

        if ($row[4] == null) {
            throw new Exception("Arquivo inválido. Endereço não informado");
        }

        //validacao data de nascimento
        if ($row[1] != null) {

            if (!preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", trim($row[1]))) {
                throw new Exception("Arquivo inválido. A data de nascimento não está no padrão XX/XX/XXXX - " . $row[0]);
            }
            list($dd, $mm, $yyyy) = explode('/', trim($row[1]));
            if (checkdate($mm, $dd, $yyyy) == false) {
                throw new Exception("Arquivo inválido. Data de nascimento inválida - " . $row[0]);
            }
        }

        //validacao telefone
        if ($row[9] != null) {
            if (strlen((string) $row[9]) < 10) {
                throw new Exception("Arquivo inválido. Número de telefone incompleto - " . $row[0]);
            }

            if (strlen((string) $row[9]) > 11) {
                throw new Exception("Arquivo inválido. Número de telefone maior que o permitido - " . $row[0]);
            }

            if (strpos((string) $row[9], "(") or strpos((string) $row[9], ")")) {
                throw new Exception("Arquivo inválido. Número de telefone contém caracteres inválidos - " . $row[0]);
            }
        }

        //valida cep
        if ($row[8] != null) {
            if (strlen((string) $row[8]) < 8) {
                throw new Exception("Arquivo inválido. CEP incompleto - " . $row[0]);
            }
            if (strlen((string) $row[8]) > 8) {
                throw new Exception("Arquivo inválido. CEP maior que o permitido - " . $row[0]);
            }
        }
    }

    private function parseChild($row)
    {

        $fieldMap = [
            0 => 'name',
            1 => 'dob',
            2 => 'mother_name',
            3 => 'father_name',
            4 => 'place_address',
            5 => 'place_neighborhood',
            8 => 'place_cep',
            9 => 'mother_phone',
            10 => 'observation'
        ];

        $data = [];

        foreach ($fieldMap as $xlsField => $systemField) {
            if (!isset($row[$xlsField])) continue;
            $data[$systemField] = trim((string) $row[$xlsField]);
        }

        $data['alert_cause_id'] = AlertCause::getBySlug('xls_import')->id;
        $data['dob'] = isset($data['dob']) ? Carbon::createFromFormat('d/m/Y', $data['dob'])->format('Y-m-d') : null;
        $data['place_uf'] = $this->tenant->city->uf;
        $data['place_city_id'] = strval($this->tenant->city->id);
        $data['place_city_name'] = $this->tenant->city->name;
        $data['place_kind'] = null;
        $data['has_been_in_school'] = false;

        $child = Child::spawnFromAlertData($this->tenant, $this->agent->id, $data);

        $pesquisa = Pesquisa::fetchWithinCase($child->current_case_id, Pesquisa::class, 20);


        $pesquisa->setFields($data);

        Comment::post($child, $this->agent, "Caso importado na planilha personalizada do Município");

        $this->total_records++;
    }

    private function isThereChild($row)
    {

        $child = Child::where(
            [
                ['name', '=', $row[0]],
                ['mother_name', '=', $row[2]],
                ['city_id', '=', $this->tenant->city_id],
                ['alert_status', '=', Child::ALERT_STATUS_ACCEPTED],
                ['child_status', '=', Child::STATUS_OUT_OF_SCHOOL]
            ]
        )->orWhere(
            [
                ['name', '=', $row[0]],
                ['mother_name', '=', $row[2]],
                ['city_id', '=', $this->tenant->city_id],
                ['alert_status', '=', Child::ALERT_STATUS_ACCEPTED],
                ['child_status', '=', Child::STATUS_OBSERVATION]
            ]
        )->orWhere(
            [
                ['name', '=', $row[0]],
                ['mother_name', '=', $row[2]],
                ['city_id', '=', $this->tenant->city_id],
                ['alert_status', '=', Child::ALERT_STATUS_PENDING]
            ]
        )->first();

        if ($child == null) {
            return false;
        } else {
            array_push($this->duplicateds, $child);
            return true;
        }
    }

    public function isHeaderPatterFileMunicipio($headerArray)
    {

        $headerPatternMunicipio = [
            0 => 'Nome da criança ou adolescente',
            1 => 'Data de nascimento: (formato dd/mm/aaaa)',
            2 => 'Nome da mãe ou responsável',
            3 => 'Nome do pai',
            4 => 'Endereço',
            5 => 'Bairro',
            6 => 'UF: (sigla do estado)',
            7 => 'Cidade',
            8 => 'CEP: (somente números) ',
            9 => 'Telefone: (apenas números com DDD)',
            10 => 'Observações'
        ];

        return $headerArray == $headerPatternMunicipio;
    }
}
