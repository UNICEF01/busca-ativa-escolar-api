<?php
/**
 * busca-ativa-escolar-api
 * EducacensoImporter.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2018
 *
 * @author Aryel Tupinamba <aryel.tupinamba@lqdi.net>
 *
 * Created at: 05/03/2018, 16:21
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
use Excel;
use Log;

class EducacensoXLSImporter implements Importer {

	const TYPE = "inep_educacenso_xls";

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

		Excel::selectSheetsByIndex(0)->load($this->file, function ($reader) { /* @var $reader \Maatwebsite\Excel\Readers\LaravelExcelReader */
			$reader->noHeading(true);

			$fieldMap = null;
			$numRecords = 0;

			Log::debug("[educacenso_import] Loading spreadsheet data into memory...");

			$data = $reader->get();

			Log::debug("[educacenso_import] Looking for data block begin...");

			foreach($data as $rowNumber => $row) {

				$firstColumn = $row->first();

				// Lines before the "UF" header is found
				if ($fieldMap === null && trim($firstColumn) !== "UF") {
					Log::debug("[educacenso_import] \t Skip row {$rowNumber}, no 'UF' keyword found: [{$firstColumn}]");
					continue;
				}

				// Found "UF" header, so data block begins here
				if ($fieldMap === null && trim($firstColumn) === "UF") {
					$fieldMap = $row;

					Log::debug("[educacenso_import] \t Found UF keyword!");
					continue;
				}

				// Empty line, so the data block is over
				if(strlen(trim($firstColumn)) <= 0) {
					Log::debug("[educacenso_import] Found empty line in data block, block has closed!");
					break;
				}

				// Map headers to keys in rows
				$mappedRow = collect($row)->mapWithKeys(function ($value, $index) use ($fieldMap) {
					if(!isset($fieldMap[$index])) return null;
					return [$fieldMap[$index] => $value];
				});

				$numRecords++;

				Log::debug("[educacenso_import] Parsing child in row {$rowNumber}... ");

				$this->parseChildRow($mappedRow);

			}

			$this->job->setTotalRecords($numRecords);

			Log::debug("[educacenso_import] Completed parsing Sheet #0!");

		});

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
			'Identificação única' => 'educacenso_id',
			'Nome do aluno' => 'name',
			'Data de nascimento' => 'dob',
			'Filiação 1' => 'mother_name',
			'Localização' => 'place_kind',
			'Código da escola' => 'school_last_id',
			'Nome da escola' => 'school_last_name',
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