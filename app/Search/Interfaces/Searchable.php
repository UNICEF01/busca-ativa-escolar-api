<?php
/**
 * busca-ativa-escolar-api
 * Searchable.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/01/2017, 21:57
 */

namespace BuscaAtivaEscolar\Search\Interfaces;


interface Searchable {

	public function getSearchIndex() : string;
	public function getSearchType() : string;
	public function getSearchID();
	public function buildSearchDocument() : array;

}