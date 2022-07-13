<?php
/**
 * busca-ativa-escolar-api
 * ChildAgeUpdated.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 26/01/2017, 15:22
 */

namespace BuscaAtivaEscolar\Events;


use BuscaAtivaEscolar\Child;
use Event;

class ChildAgeUpdated extends Event {

	public $child;
	public $prevAge;
	public $age;

	public function __construct(Child $child, $prevAge, $age) {
		$this->child = $child;
		$this->prevAge = $prevAge;
		$this->age = $age;
	}

}