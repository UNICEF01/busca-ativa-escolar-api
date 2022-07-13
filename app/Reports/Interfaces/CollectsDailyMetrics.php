<?php
/**
 * busca-ativa-escolar-api
 * CollectsDailyMetrics.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 01/02/2017, 17:30
 */

namespace BuscaAtivaEscolar\Reports\Interfaces;


interface CollectsDailyMetrics {

	public function getTimeSeriesIndex() : string;
	public function getTimeSeriesType() : string;
	public function buildMetricsDocument() : array;

}