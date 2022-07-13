<?php
/**
 * busca-ativa-escolar-api
 * NonIncrementingIndex.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 30/01/2017, 19:17
 */

namespace BuscaAtivaEscolar\Traits\Data;


trait NonIncrementingIndex {

	public function __construct() {
		$this->incrementing = false;
		call_user_func_array(['parent','__construct'], func_get_args());
	}

}