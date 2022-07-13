<?php
/**
 * busca-ativa-escolar-api
 * HandicappedRejectReason.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 10/01/2017, 16:30
 */

namespace BuscaAtivaEscolar\Data;


class HandicappedRejectReason extends StaticObject {

	protected static $data = [
		1 => ['id' => 1, 'slug' => 'family_didnt_search', 'label' => "A família não procurou uma escola"],
		2 => ['id' => 2, 'slug' => 'enrollment_denied', 'label' => "A matrícula foi negada"],
		3 => ['id' => 3, 'slug' => 'school_cannot_handle', 'label' => "A escola não tem acessibilidade para receber criança com deficiência ou Transtornos Globais de Desenvolvimento (TGD)"],
		4 => ['id' => 4, 'slug' => 'other_reasons', 'label' => "Outros motivos"],
	];

	protected static $indexes = [
		'slug' => [
			'family_didnt_search' => 1,
			'enrollment_denied' => 2,
			'school_cannot_handle' => 3,
			'other_reasons' => 4,
		]
	];

	/**
	 * @var string The reason ID
	 */
	public $id;

	/**
	 * @var string The reason slug
	 */
	public $slug;

	/**
	 * @var string The reason human-readable name
	 */
	public $label;

	/**
	 * Gets a reject reason by it's slug
	 * @param string $slug
	 * @return HandicappedRejectReason
	 */
	public static function getBySlug($slug) {
		return self::getByIndex('slug', $slug);
	}

}