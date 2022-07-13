<?php
/**
 * busca-ativa-escolar-api
 * Importer.php
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

interface Importer {

	public function handle(ImportJob $job);

}