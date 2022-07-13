<?php
/**
 * busca-ativa-escolar-api
 * IncomeRange.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 10/01/2017, 16:34
 */

namespace BuscaAtivaEscolar\Data;


class IncomeRange extends StaticObject {

	protected static $data = [
		1 => ['id' => 1, 'slug' => 'up_to_quarter', 'label' => "Até ¼"],
		2 => ['id' => 2, 'slug' => 'between_quarter_and_half', 'label' => "Mais de ¼ a ½"],
		3 => ['id' => 3, 'slug' => 'between_one_and_two', 'label' => "Mais de 1 a 2"],
		4 => ['id' => 4, 'slug' => 'over_2', 'label' => "Mais de 2"],
	];

	protected static $indexes = [
		'slug' => [
			'up_to_quarter' => 1,
			'between_quarter_and_half' => 2,
			'between_one_and_two' => 3,
			'over_2' => 4,
		]
	];

	/**
	 * @var string The income range ID
	 */
	public $id;

	/**
	 * @var string The income range slug
	 */
	public $slug;

	/**
	 * @var string The income range human-readable name
	 */
	public $label;

	/**
	 * @var float The projected minimum income in this range
	 */
	public $valueMin;

	/**
	 * @var float The projected maximum income in this range
	 */
	public $valueMax;

	/**
	 * Gets an income range by it's slug
	 * @param string $slug
	 * @return IncomeRange
	 */
	public static function getBySlug($slug) {
		return self::getByIndex('slug', $slug);
	}

}