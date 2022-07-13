<?php
/**
 * busca-ativa-escolar-api
 * AlertSpawned.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 26/01/2017, 15:37
 */

namespace BuscaAtivaEscolar\Events;


use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\Data\Gender;
use Event;

class AlertSpawned extends Event {

	public $child;
	public $case;
	public $alert;

	public function __construct(Child $child, ChildCase $case, Alerta $alert) {
		$this->child = $child;
		$this->case = $case;
		$this->alert = $alert;
	}

	public function getAgeLabel() {
		if(!$this->child->age) return 'idade desconhecida';
		if($this->child->age == 1) return '1 ano';
		return "{$this->child->age} anos";
	}

	public function getGenderLabel() {
		if(!$this->child->gender) return 'gênero desconhecido';
		return Gender::getBySlug($this->child->gender)->label ?? 'gênero desconhecido';
	}

}