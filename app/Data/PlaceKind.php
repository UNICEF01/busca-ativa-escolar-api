<?php
/**
 * busca-ativa-escolar-api
 * PlaceKind.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 06/04/2017, 17:46
 */

namespace BuscaAtivaEscolar\Data;


class PlaceKind extends StaticObject {

	protected static $data = [
		1 => ['id' => 1, 'slug' => 'urban', 'label' => "Urbano"],
		2 => ['id' => 2, 'slug' => 'rural', 'label' => "Rural"],
	];

	protected static $indexes = [
		'slug' => [
			'urban' => 1,
			'rural' => 2,
		]
	];

	/**
	 * @var integer The ID of the place kind
	 */
	public $id;

	/**
	 * @var string The slug of the place kind
	 */
	public $slug;

	/**
	 * @var string The human-readable name for the place kind
	 */
	public $label;

	/**
	 * @var integer The sequence order of the place kind
	 */
	public $order;

	/**
	 * Gets a place kind by it's slug
	 * @param string $slug
	 * @return PlaceKind
	 */
	public static function getBySlug($slug) {
		return self::getByIndex('slug', $slug);
	}

}