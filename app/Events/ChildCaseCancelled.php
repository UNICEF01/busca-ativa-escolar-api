<?php
/**
 * busca-ativa-escolar-api
 * ChildCaseCancelled.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 26/01/2017, 18:55
 */

namespace BuscaAtivaEscolar\Events;

use Event;

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\ChildCase;

class ChildCaseCancelled extends Event {

	public $child;
	public $case;
	public $reason;

	public function __construct(Child $child, ChildCase $case, $reason) {
		$this->child = $child;
		$this->case = $case;
		$this->reason = $reason;
	}

}