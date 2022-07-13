<?php
/**
 * busca-ativa-escolar-api
 * AgeRange.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2018
 *
 * @author Aryel Tupinamba <aryel.tupinamba@lqdi.net>
 *
 * Created at: 09/05/2018, 18:16
 */

namespace BuscaAtivaEscolar\Data;

class AgeRange extends StaticObject {

	protected static $data = [
		1 => ['id' => 1, 'slug' => '0-3', 'label' => "de 0 a 3 anos", 'from' => 0, 'to' => 3],
		2 => ['id' => 2, 'slug' => '4-5', 'label' => "de 4 a 5 anos", 'from' => 4, 'to' => 5],
		3 => ['id' => 3, 'slug' => '6-10', 'label' => "de 6 a 10 anos", 'from' => 6, 'to' => 10],
		4 => ['id' => 4, 'slug' => '11-14', 'label' => "de 11 a 14 anos", 'from' => 11, 'to' => 14],
		5 => ['id' => 5, 'slug' => '15-17', 'label' => "de 15 a 17 anos", 'from' => 15, 'to' => 17],
		6 => ['id' => 6, 'slug' => '18', 'label' => "mais de 18 anos", 'from' => 18, 'to' => 5000],
	];

	protected static $indexes = [
		'slug' => [
			'0-3' => 1,
			'4-5' => 2,
			'6-10' => 3,
			'11-14' => 4,
			'15-17' => 5,
			'18' => 6,
		]
	];

	/**
	 * @var string The age range ID
	 */
	public $id;

	/**
	 * @var string The age range slug
	 */
	public $slug;

	/**
	 * @var string The age range human-readable name
	 */
	public $label;

	/**
	 * @var float The minimum age in this range
	 */
	public $from;

	/**
	 * @var float The maximum age in this range
	 */
	public $to;

	/**
	 * Gets an age range by it's slug
	 * @param string $slug
	 * @return AgeRange
	 */
	public static function getBySlug($slug) {
		return self::getByIndex('slug', $slug);
	}
}