<?php
/**
 * busca-ativa-escolar-api
 * SchoolMicrodataImporter.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 30/01/2017, 17:20
 */

namespace BuscaAtivaEscolar\INEP;

use BuscaAtivaEscolar\IBGE\UF;
use BuscaAtivaEscolar\School;
use DB;
use Illuminate\Support\Str;
use Log;
use PDO;

class SchoolMicrodataImporter {

	/**
	 * @var DB
	 */
	protected $db;

	/**
	 * @var \PDO
	 */
	protected $pdo;

	/**
	 * @var Log
	 */
	protected $log;

	/**
	 * @var \PDOStatement
	 */
	protected $insertSchoolStmt;

	/**
	 * @var \PDOStatement
	 */
	protected $findCityStmt;

	public $sourceFilePath = 'static/inep_schools_2018.csv';
	public $fieldMap = [
		'CO_ENTIDADE' => 'id',
		'NO_ENTIDADE' => 'name',
		'CO_UF' => 'uf_id',
		'CO_MUNICIPIO' => 'city_ibge_id',
	];

	public function __construct(DB $db, Log $log) {
		$this->db = DB::getFacadeRoot();
		$this->log = Log::getFacadeRoot();
		$this->pdo = DB::getPdo();
	}

	public function import() {

		$this->log->info("Importing microdata from {$this->sourceFilePath}");

		try {
			$file = $this->openMicrodataFile();
			$this->processFile($file);
		} catch (\Exception $ex) {
			$this->log->error("Exception while importing microdata: {$ex->getMessage()}");
			$this->log->error($ex->getTraceAsString());
		}

	}

	protected function openMicrodataFile() {
		$path = database_path($this->sourceFilePath);

		$this->log->debug("Loading file from path: {$path}");

		if(!file_exists($path)) {
			throw new \Exception("Microdata source file missing: {$path}");
		}

		return fopen($path, "rt");
	}

	protected function processFile($file) {

		$isFirstLine = true;
		$headersMap = null;

		$this->insertSchoolStmt = $this->pdo->prepare("INSERT IGNORE INTO schools (id, name, uf_id, uf, region, city_id, city_name, city_ibge_id) VALUES (:id, :name, :uf_id, :uf, :region, :city_id, :city_name, :city_ibge_id)");
		$this->findCityStmt = $this->pdo->prepare("SELECT id, region, uf, name FROM cities WHERE ibge_city_id = ?");

		$this->log->info("Processing file lines...");

		while(($line = fgets($file)) !== false) {

			$data = explode("|", $line);

			if($isFirstLine) {
				$this->log->info("Found first line, building headers map...");
				$headersMap = $this->buildHeadersMap($data);
				$isFirstLine = false;
				continue;
			}

			$this->processLine($data, $headersMap);

		}

		$this->log->info("All lines processed!");

	}

	protected function processLine(array $rawData, array $headersMap) {
		$data = [];

		array_walk($rawData, function ($item, $key) use (&$data, $headersMap) {
			if(!isset($headersMap[$key])) return;
			$data[$headersMap[$key]] = Str::ascii($item);
		});

		if(!isset($data['id'])) {
			$this->log->info("Missing essential field 'id', skipping record: " . json_encode($rawData));
			return;
		}

		$this->log->info("Processing record: " . json_encode($data));

		$hasFoundCity = $this->findCityStmt->execute([$data['city_ibge_id']]);

		if(!$hasFoundCity) {
			$this->log->error("Skipping {$data['id']} / {$data['name']}: missing could not find city (IBGE ID={$data['city_ibge_id']}!");
			return;
		}

		$city = $this->findCityStmt->fetch(PDO::FETCH_ASSOC);

		$data['city_name'] = $city['name'] ?? null;
		$data['city_id'] = $city['id'] ?? null;
		$data['uf'] = $city['uf'] ?? null;
		$data['region'] = $city['region'] ?? null;

		$this->persistRecord($data);
	}

	protected function persistRecord(array $data) {
		//School::create($data);
		$this->insertSchoolStmt->execute($data);
	}

	protected function buildHeadersMap(array $data) {

		$map = [];
		$mappedColumns = array_keys($this->fieldMap);

		foreach($data as $columnIndex => $columnName) {
			$columnName = strtoupper($columnName);

			if(!in_array($columnName, $mappedColumns)) continue;

			$this->log->info("\tMap: #{$columnIndex} -> '{$columnName}' -> '{$this->fieldMap[$columnName]}'");

			$map[$columnIndex] = $this->fieldMap[$columnName];
		}

		$this->log->info("Headers map built!");

		return $map;

	}

}
