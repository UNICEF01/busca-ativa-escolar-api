<?php
/**
 * busca-ativa-escolar-api
 * AlertAccepted.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 09/02/2017, 20:03
 */

namespace BuscaAtivaEscolar\Events;


use BuscaAtivaEscolar\Child;
use Event;

class AlertAccepted extends Event {

	public $child;

	public function __construct(Child $child) {
		$this->child = $child;
	}

}