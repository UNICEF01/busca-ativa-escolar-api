<?php
/**
 * busca-ativa-escolar-api
 * Command.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 29/12/2016, 21:25
 */

namespace BuscaAtivaEscolar\Console\Commands;


use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\Tenant;
use Closure;
use Exception;
use Log;

abstract class Command extends \Illuminate\Console\Command {

	protected function setupLogging() {
		Log::listen(function ($level, $message, $context) {

			if($level == "error") {
				$this->error($message);
				return;
			}

			$this->comment($message);

		});
	}

	public function askForTenant($message = "Please select a tenant: ") : Tenant {

		$tenants = Tenant::all();

		// TODO: this will break when amount of tenants gets big; will need to use same searching system as cities

		if(sizeof($tenants) <= 0) {
			throw new Exception("Command requires tenant selection, but no tenants are registered!");
		}

		$this->info($message);

		foreach($tenants as $i => $tenant) {
			$this->comment("\t #{$i}: {$tenant->name} ({$tenant->id})");
		}

		$selectedTenant = $this->askUntilValid("Enter the tenant #: ", function ($index) use ($tenants) {
			return $tenants->has($index);
		});

		return $tenants[intval($selectedTenant)];

	}

	public function askForCity($message = "Please select a city: ") : City {

		$this->info($message);

		$uf = strtoupper(trim($this->ask("Enter city UF")));

		$cities = [];

		$this->askUntilValid("Enter city name (search):", function ($name) use ($uf, &$cities) {
			$cities = City::search(['uf' => $uf, 'name' => $name])->get(); /* @var $cities City[] */

			if(sizeof($cities) <= 0) {
				$this->error("No cities found in query! Try again with different search parameters.");
				return false;
			}

			return true;
		});

		$this->info("Select a city:");

		foreach($cities as $i => $city) {
			$this->comment("\t #{$i} -> {$city->uf}/{$city->name} (ID={$city->id}, IBGE_ID={$city->ibge_city_id})");
		}

		$selectedCity = $this->askUntilValid("Create tenant for city #:", function ($index) use ($cities) {
			return ($cities->has($index));
		});

		return $cities[intval($selectedCity)];
	}

	public function askUntilValid(string $question, Closure $validation) {

		$input = $this->ask($question);

		if(!$validation($input)) {
			$this->warn("Invalid option! Please try again");
			return $this->askUntilValid($question, $validation);
		}

		return $input;
	}

}