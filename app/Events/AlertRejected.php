<?php
/**
 * busca-ativa-escolar-api
 * AlertRejected.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 09/02/2017, 20:04
 */

namespace BuscaAtivaEscolar\Events;


use BuscaAtivaEscolar\Child;
use Event;

class AlertRejected extends Event {

	public $child;

	public function __construct(Child $child) {
		$this->child = $child;
	}

}