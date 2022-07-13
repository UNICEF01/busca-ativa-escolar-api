<?php
/**
 * busca-ativa-escolar-api
 * ChildCaseCloserd.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 26/01/2017, 18:55
 */

namespace BuscaAtivaEscolar\Events;


use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\ChildCase;
use Event;

class ChildCaseClosed extends Event {

	public $child;
	public $case;

	public function __construct(Child $child, ChildCase $case) {
		$this->child = $child;
		$this->case = $case;
	}

}