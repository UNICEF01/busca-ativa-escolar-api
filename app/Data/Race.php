<?php

/**
 * busca-ativa-escolar-api
 * Race.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 10/01/2017, 16:42
 */

namespace BuscaAtivaEscolar\Data;


class Race extends StaticObject
{

	protected static $data = [
		1 => ['id' => 1, 'slug' => 'indigena', 'label' => "Indígena"],
		2 => ['id' => 2, 'slug' => 'branca', 'label' => "Branca"],
		3 => ['id' => 3, 'slug' => 'preta', 'label' => "Preta"],
		4 => ['id' => 4, 'slug' => 'amarela', 'label' => "Amarela"],
		5 => ['id' => 5, 'slug' => 'no_info', 'label' => "Sem informação"],
		6 => ['id' => 6, 'slug' => 'parda', 'label' => "Parda"],
	];

	protected static $indexes = [
		'slug' => [
			'indigena' => 1,
			'branca' => 2,
			'preta' => 3,
			'amarela' => 4,
			'no_info' => 5,
			'parda' => 6
		]
	];

	/**
	 * @var string The race ID
	 */
	public $id;

	/**
	 * @var string The race slug
	 */
	public $slug;

	/**
	 * @var string The race human-readable name
	 */
	public $label;

	/**
	 * Gets a race by its slug
	 * @param string $slug
	 * @return Race
	 */
	public static function getBySlug($slug)
	{
		return self::getByIndex('slug', $slug);
	}
}
