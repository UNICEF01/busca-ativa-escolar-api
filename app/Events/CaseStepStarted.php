<?php
/**
 * busca-ativa-escolar-api
 * CaseStepStarted.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 26/01/2017, 15:17
 */

namespace BuscaAtivaEscolar\Events;


use BuscaAtivaEscolar\CaseSteps\CaseStep;
use Event;

class CaseStepStarted extends Event {

	public $step;
	public $prev;

	public function __construct(CaseStep $step, CaseStep $prev = null) {
		$this->step = $step;
		$this->prev = $prev;
	}

}