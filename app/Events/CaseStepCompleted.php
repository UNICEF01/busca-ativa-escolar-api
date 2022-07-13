<?php
/**
 * busca-ativa-escolar-api
 * CaseStepCompleted.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 26/01/2017, 15:18
 */

namespace BuscaAtivaEscolar\Events;


use BuscaAtivaEscolar\CaseSteps\CaseStep;
use Event;

class CaseStepCompleted extends Event {

	public $step;
	public $next;

	public function __construct(CaseStep $step, CaseStep $next = null) {
		$this->step = $step;
		$this->next = $next;
	}

}