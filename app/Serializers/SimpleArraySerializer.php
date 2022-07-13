<?php
/**
 * busca-ativa-escolar-api
 * SimpleArraySerializer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 08/01/2017, 24:51
 */

namespace BuscaAtivaEscolar\Serializers;

use Spatie\Fractalistic\ArraySerializer;

class SimpleArraySerializer extends ArraySerializer {

	public function collection($resourceKey, array $data) {
		if ($resourceKey === false) return $data;
		return [$resourceKey ?: 'data' => $data];
	}

	public function item($resourceKey, array $data) {
		return $data;
	}

}