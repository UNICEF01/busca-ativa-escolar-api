<?php
/**
 * busca-ativa-escolar-api
 * AlertStatusChanged.php
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

class AlertStatusChanged extends Event {

	public $child;
	public $prevStatus;
	public $newStatus;

	public function __construct(Child $child, $prevStatus, $newStatus) {
		$this->child = $child;
		$this->prevStatus = $prevStatus;
		$this->newStatus = $newStatus;
	}

}