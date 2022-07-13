<?php
/**
 * busca-ativa-escolar-api
 * Gender.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 10/01/2017, 16:49
 */

namespace BuscaAtivaEscolar\Data;


class Gender extends StaticObject {

	protected static $data = [
		1 => ['id' => 1, 'slug' => 'male', 'label' => "Masculino"],
		2 => ['id' => 2, 'slug' => 'female', 'label' => "Feminino"],
	];

	protected static $indexes = [
		'slug' => [
			'male' => 1,
			'female' => 2,
			//'undefined' => 3,
		]
	];

	/**
	 * @var string The gender ID
	 */
	public $id;

	/**
	 * @var string The gender slug
	 */
	public $slug;

	/**
	 * @var string The gender human-readable name
	 */
	public $label;

	/**
	 * Gets a gender by its slug
	 * @param string $slug
	 * @return Gender
	 */
	public static function getBySlug($slug) {
		return self::getByIndex('slug', $slug);
	}

}