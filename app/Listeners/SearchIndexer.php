<?php
/**
 * busca-ativa-escolar-api
 * SearchIndexer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 27/03/2017, 17:25
 */

namespace BuscaAtivaEscolar\Listeners;


use BuscaAtivaEscolar\Events\SearchableNeedsReindexing;
use BuscaAtivaEscolar\Search\Search;
use Log;

class SearchIndexer {

	public $search;

	public function __construct(Search $search) {
		$this->search = $search;
	}

	public function handle(SearchableNeedsReindexing $event) {
		$this->search->index($event->searchable);
	}

}