<?php
/**
 * busca-ativa-escolar-api
 * ImportCitiesFromIBGE.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 27/12/2016, 13:54
 */

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\IBGE\Importing\CityListXls;

class ImportCitiesFromIBGE extends Command {

    protected $signature = 'import:ibge_cities';
    protected $description = 'Import cities from the IBGE database';

    public function handle(CityListXls $file) {

	    $this->setupLogging();

	    $confirmed = $this->confirm("Are you sure you want to import? This will ERASE ALL CITIES currently registered in the system!");

	    if(!$confirmed) {
		    $this->error("User cancelled, aborting...");
		    return;
	    }

	    $this->info("Erasing current cities...");

	    City::truncate();

	    $this->info("Begin import process...");

	    $file->handleImport();

	    $this->info("Import process complete!");

    }
}
