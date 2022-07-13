<?php
/**
 * busca-ativa-escolar-api
 * CaseStepAssigned.php
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
use BuscaAtivaEscolar\User;
use Event;

class CaseStepAssigned extends Event {

	public $step;
	public $user;

	public function __construct(CaseStep $step, User $user) {
		$this->step = $step;
		$this->user = $user;
	}

}