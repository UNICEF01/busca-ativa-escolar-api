<?php
/**
 * busca-ativa-escolar-api
 * SerializableObject.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 13/02/2017, 18:57
 */

namespace BuscaAtivaEscolar\Data;


class SerializableObject {

	public function serialize() {
		return serialize($this);
	}

	public static function unserialize(string $serialized) {
		return unserialize($serialized);
	}

}