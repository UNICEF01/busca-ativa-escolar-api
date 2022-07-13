<?php
/**
 * busca-ativa-escolar-api
 * SearchableNeedsReindexing.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 27/03/2017, 17:23
 */

namespace BuscaAtivaEscolar\Events;


use BuscaAtivaEscolar\Search\Interfaces\Searchable;
use Event;

class SearchableNeedsReindexing extends Event {

	public $searchable;

	public function __construct(Searchable $searchable) {
		$this->searchable = $searchable;
	}

}