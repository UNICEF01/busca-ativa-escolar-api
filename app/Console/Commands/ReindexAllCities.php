<?php
/**
 * busca-ativa-escolar-api
 * ReindexAllCities.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 27/01/2017, 13:39
 */

namespace BuscaAtivaEscolar\Console\Commands;


use BuscaAtivaEscolar\City;

class ReindexAllCities extends Command {

	protected $signature = 'maintenance:reindex_all_cities';
	protected $description = 'Forces all cities in the system to be reindex in ElasticSearch';

	public function handle() {
		$cities = City::with('tenant')->get();

		foreach($cities as $city) {
			$this->comment("Reindexing: {$city->id} -> {$city->uf} / {$city->name}");
			$city->save();
		}

		$this->comment("All cities reindexed!");

	}

}