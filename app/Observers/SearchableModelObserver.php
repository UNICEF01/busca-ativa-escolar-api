<?php
/**
 * busca-ativa-escolar-api
 * SearchableModelObserver.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/01/2017, 22:28
 */

namespace BuscaAtivaEscolar\Observers;

use BuscaAtivaEscolar\Search\Interfaces\Searchable;
use BuscaAtivaEscolar\Search\Search;

class SearchableModelObserver {

	private $search;

	public function __construct(Search $search) {
		$this->search = $search;
	}

	public function saved(Searchable $searchable) {
		$this->search->index($searchable);
	}

	public function deleted(Searchable $searchable) {
		$this->search->delete($searchable);
	}
}