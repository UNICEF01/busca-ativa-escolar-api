<?php
/**
 * busca-ativa-escolar-api
 * GenericTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 13/02/2017, 19:18
 */

namespace BuscaAtivaEscolar\Transformers;

use League\Fractal\TransformerAbstract;

class GenericTransformer extends TransformerAbstract {

	public function transform($data) {
		if(is_array($data)) return $data;
		if(is_object($data)) return (array) $data;
		throw new \Exception('Cannot apply generic transformation to a non-object');
	}

}