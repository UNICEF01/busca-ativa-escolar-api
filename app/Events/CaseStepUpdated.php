<?php
/**
 * busca-ativa-escolar-api
 * CaseStepUpdated.php
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

class CaseStepUpdated extends Event {

	public $step;
	public $data;

	public function __construct(CaseStep $step, array $data) {
		$this->step = $step;
		$this->data = $data;
	}

}