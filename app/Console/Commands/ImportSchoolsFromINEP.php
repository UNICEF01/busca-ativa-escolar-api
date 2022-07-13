<?php
/**
 * busca-ativa-escolar-api
 * ImportSchoolsFromINEP.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 30/01/2017, 18:07
 */

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\INEP\SchoolMicrodataImporter;

class ImportSchoolsFromINEP extends Command {

	protected $signature = 'import:inep_schools';
	protected $description = 'Import cities from the IBGE database';

	public function handle(SchoolMicrodataImporter $importer) {

		$this->setupLogging();

		$importer->import();

	}

}