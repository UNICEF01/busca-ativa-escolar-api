<?php
/**
 * busca-ativa-escolar-api
 * GenerateStaticDataSQL.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 12/07/2017, 19:16
 */

namespace BuscaAtivaEscolar\Console\Commands;


use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\School;
use Illuminate\Support\Str;

class GenerateStaticDataSQL extends Command {

	protected $signature = "maintenance:generate_static_data_sql";
	protected $description = "";

	public function handle() {

		set_time_limit(0);
		ini_set('memory_limit', '1G');

		ob_implicit_flush(true);

		$citiesCreate = "
			CREATE VIRTUAL TABLE cities_idx USING fts4 (
				id VARCHAR(255),
				search_string VARCHAR(255),
				display_name VARCHAR(255),
				name VARCHAR(255),
			);\n\n";

		$schoolsCreate = "
			CREATE VIRTUAL TABLE schools_idx USING fts4 (
				id VARCHAR(255),
				name VARCHAR(255),
			);\n\n";

		$cities = City::all();
		$numCities = sizeof($cities);

		$fileName = 'static_data_export.sql';

		if(file_exists(storage_path($fileName))) {
			unlink(storage_path($fileName));
		}

		$sql = fopen(storage_path($fileName), "w+");

		fwrite($sql, $citiesCreate);
		fwrite($sql, $schoolsCreate);

		fwrite($sql, "\nINSERT INTO cities_idx (id, search_string, display_name, name) VALUES \n");

		foreach($cities as $i => $city) {
			$searchString = str_replace('\\\'', '\'\'', addslashes($city->name . " " . Str::ascii($city->name)));
			$city->name = str_replace('\\\'', '\'\'', addslashes($city->name));

			$comma = ($i === $numCities-1) ? "\n\n" : ",\n";

			$this->comment("City #{$city->id} - {$city->name}");

			fwrite($sql, "('{$city->id}', '{$searchString}', '{$city->name} / {$city->uf}', '{$city->name}'){$comma}");
		}

		unset($cities);

		$schools = School::all();
		$numSchools = sizeof($schools);

		fwrite($sql, "\n\nINSERT INTO schools_idx (id, name) VALUES \n");

		foreach($schools as $i => $school) {

			$school->name = str_replace('\\\'', '\'\'', addslashes($school->name));

			$comma = ($i === $numSchools-1) ? "\n\n" : ",\n";

			$this->comment("School #{$school->id} - {$school->name}");

			fwrite($sql, "('{$school->id}', '{$school->name}'){$comma}");

		}

	}

}