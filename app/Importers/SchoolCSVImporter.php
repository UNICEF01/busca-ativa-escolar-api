<?php
/**
 * busca-ativa-escolar-api
 * SchoolCSVImporter.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 05/04/2017, 17:00
 */

namespace BuscaAtivaEscolar\Importers;


use BuscaAtivaEscolar\ImportJob;
use BuscaAtivaEscolar\School;
use BuscaAtivaEscolar\Search\Search;
use DB;
use Log;
use PDO;

class SchoolCSVImporter implements Importer {

	const TYPE = "school_csv";

	/**
	 * @var DB
	 */
	private $db;

	/**
	 * @var Log
	 */
	private $log;

	/**
	 * @var PDO
	 */
	private $pdo;

	/**
	 * @var int The given records' offset to begin importing
	 */
	private $offset = 0;

	/**
	 * @var array The header alignment map
	 */
	private $headers = null;

	/**
	 * @var Search The search index service
	 */
	private $search;

	/**
	 * @var \PDOStatement Stored statement to spawn schools
	 */
	private $insertSchoolStmt;

	/**
	 * @var \PDOStatement Stored statement to find cities by INEP ID
	 */
	private $findCityStmt;


	public function __construct() {
		$this->db = DB::getFacadeRoot();
		$this->log = Log::getFacadeRoot();
		$this->pdo = DB::getPdo();
		$this->search = app('BuscaAtivaEscolar\Search\Search');

		$this->insertSchoolStmt = $this->pdo->prepare("INSERT IGNORE INTO schools (id, name, uf_id, uf, region, city_id, city_name, city_ibge_id) VALUES (:id, :name, :uf_id, :uf, :region, :city_id, :city_name, :city_ibge_id)");
		$this->findCityStmt = $this->pdo->prepare("SELECT id, region, uf, name FROM cities WHERE ibge_city_id = ?");
	}

	/**
	 * @param ImportJob $job
	 * @throws \Throwable
	 */
	public function handle(ImportJob $job) {

		$this->offset = $job->offset;
		$this->checkRecordCount($job);


		$logEvery = ceil($job->total_records / 1000);

		try {

			$fp = fopen($job->getAbsolutePath(), "r");
			$current = 0;

			$this->log->info("Processing file: {$job->getAbsolutePath()}");
			$this->log->info("Persisting offset every {$logEvery} records");

			while(($line = fgetcsv($fp, 4096, '|')) !== false) {

				if($current < $this->offset) continue;

				$this->handleLine($line);

				$current++;

				if($current % $logEvery === 0) {
					$job->setOffset($current);
				}

			}

			$this->log->info("File processed! {$current} records processed.");

			$this->offset = $current;
			$job->setOffset($this->offset);

		} catch (\Throwable $ex) {
			$this->log->error("Error while processing file (at offset={$this->offset}): {$ex->getMessage()}");
			$job->setOffset($this->offset);

			throw $ex;
		}

	}

	protected function handleLine(array $line) {
		if(!$line) return;
		if(sizeof($line) <= 0) return;
		if(sizeof($line) == 1 && $line[0] === null) return;

		if($this->headers === null) {
			$this->headers = [];
			foreach($line as $columnIndex => $columnName) {
				$this->headers[strtolower(trim($columnName))] = $columnIndex;
			}

			$this->log->info("Mapped headers: " . json_encode($this->headers));

			return;
		}

		$headers = $this->headers;

		if(!isset($line[$headers['co_entidade']])) {
			$this->log->info("Missing essential field 'co_entidade', skipping record: " . json_encode($line));
			return;
		}

		$hasFoundCity = $this->findCityStmt->execute([$line[$headers['co_municipio']]]);

		if(!$hasFoundCity) {
			$this->log->error("Skipping {$line[$headers['co_entidade']]} / {$line[$headers['no_entidade']]}: missing could not find city (IBGE ID={$line[$headers['city_ibge_id']]}!");
			return;
		}

		$city = $this->findCityStmt->fetch(PDO::FETCH_ASSOC);

		//$this->log->info("Processing record: " . json_encode($line));

		$data = [
			'id' => $line[$headers['co_entidade']] ?? null,
			'name' => $line[$headers['no_entidade']] ?? null,
			'city_id' => $city['id'] ?? null,
			'city_name' => $city['name'] ?? null,
			'uf' => $city['uf'] ?? null,
			'region' => $city['region'] ?? null,
			'uf_id' => $line[$headers['co_uf']] ?? null,
			'city_ibge_id' => $line[$headers['co_municipio']] ?? null,
		];

		$this->insertSchoolStmt->execute($data);

		$school = new School();
		$school->id = $data['id'];
		$school->fill($data);

		$this->search->index($school);

		Log::info($data["name"]." indexed");

	}

	private function checkRecordCount(ImportJob $job) {
		$lineCountPath = escapeshellarg($job->getAbsolutePath());
		$totalRecords = intval(exec("wc -l {$lineCountPath} | grep -o [0-9]*\ "));

		$job->setTotalRecords($totalRecords);
	}
}