<?php
/**
 * busca-ativa-escolar-api
 * ChildCaseCompleted.php
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

class ChildCaseCompleted extends Event {

	public $child;
	public $case;

	public function __construct(Child $child, ChildCase $case) {
		$this->child = $child;
		$this->case = $case;
	}

}