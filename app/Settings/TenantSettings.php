<?php
/**
 * busca-ativa-escolar-api
 * TenantSettings.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 13/02/2017, 18:39
 */

namespace BuscaAtivaEscolar\Settings;


use BuscaAtivaEscolar\CaseSteps\CaseStep;
use BuscaAtivaEscolar\Data\AlertCause;
use BuscaAtivaEscolar\Data\SerializableObject;

class TenantSettings extends SerializableObject {

	public $alertPriorities = [
		10 =>  'high', // Adolescente em conflito com a lei
		20 =>  'high', // Criança ou adolescente com deficiência(s)
		30 =>  'medium', // Criança ou adolescente com doença(s) que impeça(m) ou dificulte(m) a frequência à escola
		40 =>  'high', // Criança ou adolescente em abrigo
		50 =>  'high', // Criança ou adolescente em situação de rua
		60 =>  'high', // Criança ou adolescente vítima de abuso / violência sexual
		61 =>  'high', // Crianças ou adolescentes migrantes estrangeiros
		70 =>  'low', // Evasão porque sente a escola desinteressante
		80 =>  'low', // Falta de documentação da criança ou adolescente
		90 =>  'medium', // Falta de infraestrutura escolar
		100 =>  'low', // Falta de transporte escolar
		110 =>  'medium', // Gravidez na adolescência
		111 =>  'medium', // Gravidez na adolescência
		120 =>  'high', // Preconceito ou discriminação racial
		130 =>  'high', // Trabalho infantil
		140 =>  'high', // Uso, abuso ou dependência de substâncias psicoativas
		150 =>  'high', // Violência familiar
		160 =>  'high', // Violência na escola
		500 =>  'medium', // Planilha Educacenso
	];

	public $stepDeadlines = [
		'alerta' => 15,
		'pesquisa' => 15,
		'analise_tecnica' => 5,
		'gestao_do_caso' => 30,
		'rematricula' => 30,
		'1a_observacao' => 60,
		'2a_observacao' => 60,
		'3a_observacao' => 60,
		'4a_observacao' => 60,
	];

	const BLOCKED_STEP_DEADLINES = [];

	public function getAlertPriority($cause_id) {
		if(!isset($this->alertPriorities[intval($cause_id)])) return null;
		return $this->alertPriorities[intval($cause_id)];
	}

	public function getStepDeadline($step_slug) {
		if(!isset($this->stepDeadlines[$step_slug])) return null;
		return $this->stepDeadlines[$step_slug];
	}

	protected function updateAlertPriorities($priorities) {
		if(!is_array($priorities)) throw new \InvalidArgumentException('invalid_format');

		foreach($priorities as $alertCauseID => $alertPriority) {

			$alertCauseID = intval($alertCauseID);

			if(!AlertCause::getByID($alertCauseID)) throw new \InvalidArgumentException('invalid_alert_cause_id');
			if(!in_array($alertPriority, ['low', 'medium', 'high'])) throw new \InvalidArgumentException('invalid_priority');

			$this->alertPriorities[$alertCauseID] = $alertPriority;

		}
	}

	protected function updateStepDeadlines($deadlines) {
		if(!is_array($deadlines)) throw new \InvalidArgumentException('invalid_format');

		foreach($deadlines as $stepSlug => $stepDeadline) {

			if(!in_array($stepSlug, CaseStep::SLUGS)) throw new \InvalidArgumentException('invalid_step_slug');

			$this->stepDeadlines[$stepSlug] = intval($stepDeadline);

		}
	}

	public function update($data) {

		if(isset($data['alertPriorities'])) $this->updateAlertPriorities($data['alertPriorities']);
		if(isset($data['stepDeadlines'])) $this->updateStepDeadlines($data['stepDeadlines']);

	}



}