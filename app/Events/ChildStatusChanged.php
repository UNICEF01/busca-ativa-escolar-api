<?php
/**
 * busca-ativa-escolar-api
 * ChildStatusChanged.php
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

class ChildStatusChanged extends Event {

	public $child;
	public $prevStatus;
	public $newStatus;

	public function __construct(Child $child, $prevStatus, $newStatus) {
		$this->child = $child;
		$this->prevStatus = $prevStatus;
		$this->newStatus = $newStatus;
	}

}