<?php
/**
 * busca-ativa-escolar-api
 * CanBeAggregated.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 01/02/2017, 17:30
 */

namespace BuscaAtivaEscolar\Reports\Interfaces;

interface CanBeAggregated {
	public function getAggregationIndex() : string;
	public function getAggregationType() : string;
	public function buildAggregationDocument() : array;
}