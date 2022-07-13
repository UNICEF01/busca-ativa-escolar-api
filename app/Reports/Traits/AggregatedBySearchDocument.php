<?php
/**
 * busca-ativa-escolar-api
 * AggregatedBySearchDocument.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 01/02/2017, 17:34
 */

namespace BuscaAtivaEscolar\Reports\Traits;


trait AggregatedBySearchDocument {

	public function getAggregationIndex() : string {
		return $this->getSearchIndex();
	}
	public function getAggregationType() : string {
		return $this->getSearchType();

	}
	public function buildAggregationDocument() : array {
		return $this->buildSearchDocument();
	}

}