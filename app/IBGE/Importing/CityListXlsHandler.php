<?php
/**
 * busca-ativa-escolar-api
 * CityXLSImporter.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 27/12/2016, 13:06
 */

namespace BuscaAtivaEscolar\IBGE\Importing;

use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\IBGE\UF;

use Log;

use Maatwebsite\Excel\Collections\CellCollection;
use Maatwebsite\Excel\Collections\SheetCollection;
use Maatwebsite\Excel\Files\ImportHandler;
use Webpatser\Uuid\Uuid;

class CityListXlsHandler implements ImportHandler {

	public function handle($file) {

		$currentCityCount = City::count();

		if($currentCityCount > 0) {
			throw new \Exception("Cannot import cities from IBGE database: cities already exist in the platform. You must wipe the city database before importing.");
		}

		Log::info("Reading IBGE database file...");

		$sheets = $file->get(); /* @var $sheets SheetCollection */
		$sheet = $sheets->first(); /* @var $sheet \Maatwebsite\Excel\Collections\RowCollection */

		Log::info("File read, parsing results...");

		$stats = [
			'numImported' => 0,
			'failedCities' => [],
		];

		$timestamp = date('Y-m-d H:i:s');

		$sheet->each(function (CellCollection $item) use ($timestamp, $stats) {

			if(strlen(trim($item->get(CityListXls::FIELD_IBGE_CITY_ID))) <= 0) {
				Log::info("Skipping empty line / line missing ID...");
				return;
			}

			try {

				$uf = UF::getByID(intval($item->get(CityListXls::FIELD_IBGE_UF_ID)));

				City::insert([
					'id' => Uuid::generate(5, $item->get(CityListXls::FIELD_IBGE_CITY_ID), Uuid::NS_OID),

					'uf' => $item->get(CityListXls::FIELD_UF_CODE),
					'region' => $uf->getRegion()->code,

					'name' => ucfirst($item->get(CityListXls::FIELD_NAME)),

					'ibge_city_id' => intval($item->get(CityListXls::FIELD_IBGE_CITY_ID)),
					'ibge_uf_id' => intval($item->get(CityListXls::FIELD_IBGE_UF_ID)),
					'ibge_region_id' => $uf->region_id,

					'created_at' => $timestamp,
					'updated_at' => $timestamp,
				]);

				$stats['numImported']++;

				Log::info("Imported city: " . $item->get(CityListXls::FIELD_IBGE_CITY_ID) . " - " . ucfirst($item->get(CityListXls::FIELD_NAME)));

			} catch (\Exception $ex) {
				Log::error("Failed importing: " . $item->get(CityListXls::FIELD_IBGE_CITY_ID));
				array_push($failedCities, intval($item->get(CityListXls::FIELD_IBGE_CITY_ID)));
			}

		});

		Log::info("Failed cities (" . sizeof($stats['failedCities']) . " total): " . join(", ", $stats['failedCities']));
		Log::info("{$stats['numImported']} cities imported from IBGE database file!");
	}
}