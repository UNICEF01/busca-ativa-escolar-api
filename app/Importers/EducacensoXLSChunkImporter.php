<?php

namespace BuscaAtivaEscolar\Importers;

use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Comment;
use BuscaAtivaEscolar\Data\AlertCause;
use BuscaAtivaEscolar\Importers\TypeImporters\ChunkEducacensoReadFilter;
use BuscaAtivaEscolar\ImportJob;
use BuscaAtivaEscolar\School;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DB;

const MAX_ROW = 65536;
const HEADER_ROW = 12;
const DATA_START_ROW = 13;

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
     * @var int The year of Educacenso
     * This is ony for register. The real value is registered in Children created_at.
     */
    public $educacenso_year = 0;
    /**
     * @var User The agent that is identified as the creator of the alerts
     */
    private $agent;

    /**
     * Manipula a importação do Educacenso a partir de um arquivo XLS
     * @param ImportJob $job - O trabalho de importação enviado
     * @throws Exception
     */
    public function handle(ImportJob $job)
    {
        try {

            $this->job = $job;
            $this->tenant = $job->tenant;
            $this->file = $job->getAbsolutePath();

            $this->agent = User::find(User::ID_EDUCACENSO_BOT);

            if (!$this->agent) {
                throw new Exception("Failed to find Educacenso bot user!");
            }

            /** Cria o reader do PhpSpreadsheet */
            $reader = IOFactory::createReader('Xlsx');

            /**  Define a quantidade de linhas para cada chunk  **/
            $CHUNKSIZE = 100;

            /**  Instância de filtro ChunkEducacensoReadFilter **/
            $chunkFilter = new ChunkEducacensoReadFilter();

            $reader->setReadFilter($chunkFilter);

            $this->job->total_records = 0;

            for ($startRow = 0; $startRow <= MAX_ROW; $startRow += $CHUNKSIZE) {

                $chunkFilter->setRows($startRow, $CHUNKSIZE);
                $maxRow = ($startRow + $CHUNKSIZE) - 1;
                $spreadsheet = $reader->load($this->file);
                $records = $spreadsheet->getActiveSheet()->rangeToArray('A' . $startRow . ':N' . $maxRow);

                if ($startRow > 0 and $records[0][0] == null) {
                    break;
                }

                if ($startRow == 0) {

                    $this->educacenso_year = $this->getEducacensoYear($records[5][1]);

                    if (!$this->isEducacensoHeader($records[HEADER_ROW])) {
                        throw new Exception("Cabeçalho padrão do Educacenso não localizado - Arquivo pode estar fora do padrão");
                    }

                    if ($this->isFirstDataRowEmpty($records[DATA_START_ROW])) {
                        throw new Exception("Arquivo correto, porém com primeira linha de informações vazia");
                    }

                    // Primeiro bloco de 100 valores. Inicia a leitura na linha 13
                    $this->processRecords($records, $startRow, HEADER_ROW);
                } else {
                    // Segundo bloco de 100 valores em diante. Inicia a leitura na linha 0
                    $this->processRecords($records, $startRow);
                }
            }

            $this->tenant->educacenso_import_details = [
                'has_imported' => true,
                'imported_at' => date('Y-m-d H:i:s'),
                'last_job_id' => $this->job->id,
                'file' => $this->file
            ];

            $this->tenant->save();

        } catch (Exception $e) {
            Log::error("Erro durante a importação do Educacenso: " . $e->getMessage());
            throw new Exception("Erro durante a importação do Educacenso. Entre em contato com o Suporte.");
        }
    }

    /**
     * Extrai o ano do Educacenso a partir de um texto fornecido usando uma expressão regular
     * @param String $text - O texto a ser analisado
     * @return string - O ano extraído como string
     * @throws Exception
     */
    function getEducacensoYear($text)
    {
        $patternYear = '/\b\d+\b/';
        preg_match($patternYear, $text, $matches);
        if (isset($matches[0])) {
            return $matches[0];
        } else {
            throw new Exception("Ano do Educacenso não localizado - Arquivo pode estar fora do padrão");
        }
    }

    /**
     * Verifica se o cabeçalho do Educacenso no arquivo XLS corresponde ao cabeçalho esperado
     * @param array $headerArray - O array representando o cabeçalho
     * @return bool - Verdadeiro se o cabeçalho estiver correto, falso caso contrário
     */
    public function isEducacensoHeader($headerArray)
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

    /**
     * Verifica se a primeira linha de dados no arquivo XLS está vazia
     * @param array $dataRow - A linha de dados a ser verificada
     * @return bool - Verdadeiro se a primeira célula estiver vazia, falso caso contrário
     */
    function isFirstDataRowEmpty($dataRow)
    {
        // Verifica se a primeira célula da primeira linha de dados está vazia
        if ($dataRow[0] == null) {
            return true;
        }
        return false;
    }

    /**
     * Processa registros em pedaços, inserindo-os no banco de dados
     * @param array $records - Os registros a serem processados
     * @param int $startRow - A linha inicial de processamento
     * @param int|null $keyLimit - Limite opcional de chaves a serem processadas
     * @throws Exception
     */
    function processRecords($records, $startRow, $keyLimit = null)
    {
        DB::beginTransaction();
        try {
            foreach ($records as $key => $record) {
                if ($keyLimit !== null && $key <= $keyLimit) {
                    continue;
                }
                $parsedChild = $this->parseDataXlsToSystemFields($record);
                if ($parsedChild == null) {
                    DB::commit();
                    break;
                }
                $this->insertRecord($parsedChild);
                $this->job->total_records++;
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw new Exception("Erro ao inserir os dados. Entre em contato com o Suporte.");
        }
    }

    /**
     * Analisa dados do XLS para campos do sistema
     * @param array $xlsData - Os dados do XLS a serem analisados
     * @return array|null - Um array de campos do sistema ou nulo se os dados estiverem incompletos ou ausentes
     */
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

		// Verifica se o campo mother_name atende às condições especificadas
		if ($this->isInvalidMotherName(trim($data['mother_name']))) {
			$data['mother_name'] = null;
		}

        foreach ($fieldMap as $xlsField => $systemField) {
            if (!isset($data[$systemField]) || $data[$systemField] === null) {
                return null;
            }
        }

        return $data;
    }


	/**
	 * Verifica se o nome da mãe é inválido
	 * @param string $str - O nome da mãe a ser verificado
	 * @return bool - Verdadeiro se o nome for inválido, falso caso contrário
	 */
	public function isInvalidMotherName($str)
	{
		// Verifica se o campo é nulo, vazio, possui menos de 4 caracteres, possui caracteres especiais ou letras repetidas 3 vezes consecutivas
		if (is_null($str) || strlen($str) < 4 || trim($str) == '' || preg_match('/[^a-zA-Z\u00C0-\u00FF\s]/', $str) || preg_match('/(.)\\1{2}/', $str)) {
			return true;
		}
		return false;
	}

    /**
     * Insere um registro no banco de dados
     * @param array $data - Os dados a serem inseridos
     * @throws Exception
     */
    public function insertRecord($data)
    {
        $codigoEscola = $data['school_last_id'];

        $result = School::where('id', (int)$codigoEscola)->first();

        if (!empty($result) && is_numeric($result->id)) {
            $data['place_city_id'] = $result->id;
            $data['place_city_name'] = $result->city_name;
            $data['place_uf'] = $result->uf;
            $data['observation'] = "Escola: " . $data['school_last_name'] . " | Modalidade de ensino: " . $data['modalidade'] . " | Etapa: " . $data['etapa'];
        } else {
            $data['place_uf'] = $this->tenant->city->uf;
            $data['place_city_id'] = strval($this->tenant->city->id);
            $data['place_city_name'] = $this->tenant->city->name;
            $data['observation'] = "ESCOLA NÃO ENCONTRADA NO SISTEMA: " . $data['school_last_name'] . " | Modalidade de ensino: " . $data['modalidade'] . " | Etapa: " . $data['etapa'];
        }

        $data['alert_cause_id'] = AlertCause::getBySlug('educacenso_inep')->id;
        $data['educacenso_id'] = strval($data['educacenso_id'] ?? "unkn_" . uniqid());
        $data['name'] = $data['name'];
        $data['dob'] = isset($data['dob']) ? Carbon::createFromFormat('d/m/Y', $data['dob'])->format('Y-m-d') : null;
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
}
