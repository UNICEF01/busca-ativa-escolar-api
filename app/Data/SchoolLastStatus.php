<?php
/**
 * busca-ativa-escolar-api
 * SchoolLastStatus.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 06/04/2017, 17:42
 */

namespace BuscaAtivaEscolar\Data;


class SchoolLastStatus extends StaticObject {

	protected static $data = [
		1 => ['id' => 1, 'slug' => 'completed', 'label' => "Completo"],
		2 => ['id' => 2, 'slug' => 'pending', 'label' => "Incompleto"],
	];

	protected static $indexes = [
		'slug' => [
			'completed' => 1,
			'pending' => 2,
		]
	];

	/**
	 * @var integer The ID of the status
	 */
	public $id;

	/**
	 * @var string The slug of the status
	 */
	public $slug;

	/**
	 * @var string The human-readable name for the status
	 */
	public $label;

	/**
	 * Gets a status by it's slug
	 * @param string $slug
	 * @return SchoolLastStatus
	 */
	public static function getBySlug($slug) {
		return self::getByIndex('slug', $slug);
	}

}