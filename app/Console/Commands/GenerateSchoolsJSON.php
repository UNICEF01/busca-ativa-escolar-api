<?php
/**
 * busca-ativa-escolar-api
 * GenerateSchoolsJSON.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 20/06/2017, 14:21
 */

namespace BuscaAtivaEscolar\Console\Commands;


use BuscaAtivaEscolar\School;

class GenerateSchoolsJSON extends Command {

	protected $signature = "maintenance:generate_schools_json";
	protected $description = "Generates a raw JSON file with the list of schools (for bundling in the mobile app)";

	public function handle() {

		set_time_limit(0);
		ini_set('memory_limit', '1G');

		ob_implicit_flush(true);

		$schools = School::query()->get(['id', 'name', 'uf', 'city_id', 'city_name', 'city_ibge_id']);


		echo json_encode($schools);

	}

}