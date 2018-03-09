<?php
/**
 * busca-ativa-escolar-api
 * AlertCause.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 28/12/2016, 13:16
 */

namespace BuscaAtivaEscolar\Data;

class AlertCause extends StaticObject  {

	protected static $data = [
		10 => ['id' => 10, 'sms_index' => 1, 'slug' => 'adolescente_em_conflito_com_a_lei', 'label' => "Adolescente em conflito com a lei", "case_cause_ids" => [10]],
		20 => ['id' => 20, 'sms_index' => 2, 'slug' => 'crianca_com_deficiencia', 'label' => "Criança ou adolescente com deficiência(s)", "case_cause_ids" => [21, 22, 23, 24]],
		30 => ['id' => 30, 'sms_index' => 3, 'slug' => 'crianca_com_doencas', 'label' => "Criança ou adolescente com doença(s) que impeça(m) ou dificulte(m) a frequência à escola", "case_cause_ids" => [30]],
		40 => ['id' => 40, 'sms_index' => 4, 'slug' => 'crianca_em_abrigo', 'label' => "Criança ou adolescente em abrigo", "case_cause_ids" => [40]],
		50 => ['id' => 50, 'sms_index' => 5, 'slug' => 'crianca_na_rua', 'label' => "Criança ou adolescente em situação de rua", "case_cause_ids" => [50]],
		60 => ['id' => 60, 'sms_index' => 6, 'slug' => 'crianca_vitima_abuso', 'label' => "Criança ou adolescente vítima de abuso / violência sexual", "case_cause_ids" => [60]],
		70 => ['id' => 70, 'sms_index' => 7, 'slug' => 'evasao_desinteresse', 'label' => "Evasão porque sente a escola desinteressante", "case_cause_ids" => [70]],
		80 => ['id' => 80, 'sms_index' => 8, 'slug' => 'falta_documentacao', 'label' => "Falta de documentação da criança ou adolescente", "case_cause_ids" => [80]],
		90 => ['id' => 90, 'sms_index' => 9, 'slug' => 'falta_infraestrutura', 'label' => "Falta de infraestrutura escolar", "case_cause_ids" => [91, 92]],
		100 => ['id' => 100, 'sms_index' => 10, 'slug' => 'falta_transporte', 'label' => "Falta de transporte escolar", "case_cause_ids" => [100]],
		110 => ['id' => 110, 'sms_index' => 11, 'slug' => 'gravidez_adolescencia', 'label' => "Gravidez na adolescência", "case_cause_ids" => [110]],
		120 => ['id' => 120, 'sms_index' => 12, 'slug' => 'preconceito_racial', 'label' => "Preconceito ou discriminação racial", "case_cause_ids" => [120]],
		130 => ['id' => 130, 'sms_index' => 13, 'slug' => 'trabalho_infantil', 'label' => "Trabalho infantil", "case_cause_ids" => [130]],
		140 => ['id' => 140, 'sms_index' => 14, 'slug' => 'uso_substancias', 'label' => "Uso, abuso ou dependência de substâncias psicoativas", "case_cause_ids" => [140]],
		150 => ['id' => 150, 'sms_index' => 15, 'slug' => 'violencia_familiar', 'label' => "Violência familiar", "case_cause_ids" => [150]],
		160 => ['id' => 160, 'sms_index' => 16, 'slug' => 'violencia_escolar', 'label' => "Violência na escola", "case_cause_ids" => [161, 162, 163]],
		500 => ['id' => 500, 'sms_index' => null, 'slug' => 'educacenso_inep', 'label' => "Evasão reportada pelo Educacenso/INEP", "case_cause_ids" => [500], 'hidden' => true],
	];

	protected static $indexes = [
		'slug' => [
			'adolescente_em_conflito_com_a_lei' => 10,
			'crianca_com_deficiencia' => 20,
			'crianca_com_doencas' => 30,
			'crianca_em_abrigo' => 40,
			'crianca_na_rua' => 50,
			'crianca_vitima_abuso' => 60,
			'evasao_desinteresse' => 70,
			'falta_documentacao' => 80,
			'falta_infraestrutura' => 90,
			'falta_transporte' => 100,
			'gravidez_adolescencia' => 110,
			'preconceito_racial' => 120,
			'trabalho_infantil' => 130,
			'uso_substancias' => 140,
			'violencia_familiar' => 150,
			'violencia_escolar' => 160,
			'educacenso_inep' => 500,
		],
		'sms_index' => [
			1 => 10,
			2 => 20,
			3 => 30,
			4 => 40,
			5 => 50,
			6 => 60,
			7 => 70,
			8 => 80,
			9 => 90,
			10 => 100,
			11 => 110,
			12 => 120,
			13 => 130,
			14 => 140,
			15 => 150,
			16 => 160,
		]
	];

	/**
	 * @var integer The ID of the alert cause
	 */
	public $id;

	/**
	 * @var string The slug of the alert cause
	 */
	public $slug;

	/**
	 * @var string The human-readable name for the alert cause
	 */
	public $label;

	/**
	 * @var integer The index of the alert cause in the SMS listing
	 */
	public $sms_index;

	/**
	 * @var array The list of IDs of the case causes implied by this alert cause
	 */
	public $case_cause_ids;

	/**
	 * @var bool Is this cause hidden from user selection?
	 */
	public $hidden;

	/**
	 * Gets an alert cause by it's slug
	 * @param string $slug
	 * @return AlertCause
	 */
	public static function getBySlug($slug) {
		return self::getByIndex('slug', $slug);
	}

	/**
	 * Gets an alert cause by it's SMS index
	 * @param integer $index
	 * @return AlertCause
	 */
	public static function getBySMSIndex($index) {
		$index = intval($index);
		return self::getByIndex('sms_index', $index);
	}

	/**
	 * Gets all alert causes that are visible for user selection
	 * @return array
	 */
	public static function getAllVisible() {
		return collect(self::getAllAsArray())
			->filter(function ($cause) {
				return !isset($cause['hidden']) || !boolval($cause['hidden']);
			})
			->toArray();
	}

}