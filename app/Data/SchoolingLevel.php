<?php
/**
 * busca-ativa-escolar-api
 * SchoolingLevel.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 10/01/2017, 16:39
 */

namespace BuscaAtivaEscolar\Data;


class SchoolingLevel extends StaticObject {

	protected static $data = [
		1 => ['id' => 1, 'slug' => 'nenhuma', 'label' => "Nenhuma"],
		2 => ['id' => 2, 'slug' => 'ef_incompleto', 'label' => "Ensino fundamental incompleto"],
		3 => ['id' => 3, 'slug' => 'ef_completo', 'label' => "Ensino fundamental completo"],
		4 => ['id' => 4, 'slug' => 'em_incompleto', 'label' => "Ensino médio incompleto"],
		5 => ['id' => 5, 'slug' => 'em_completo', 'label' => "Ensino médio completo"],
		6 => ['id' => 6, 'slug' => 'superior_incompleto', 'label' => "Ensino superior incompleto"],
		7 => ['id' => 7, 'slug' => 'superior_completo', 'label' => "Ensino superior completo"],
		8 => ['id' => 8, 'slug' => 'posgraduacao', 'label' => "Pós graduação"],
	];

	protected static $indexes = [
		'slug' => [
			'nenhuma' => 1,
			'ef_incompleto' => 3,
			'ef_completo' => 2,
			'em_incompleto' => 5,
			'em_completo' => 4,
			'superior_incompleto' => 7,
			'superior_completo' => 6,
			'posgraduacao' => 8,
		]
	];

	/**
	 * @var string The schooling level ID
	 */
	public $id;

	/**
	 * @var string The schooling level slug
	 */
	public $slug;

	/**
	 * @var string The schooling level human-readable name
	 */
	public $label;

	/**
	 * Gets a schooling level by its slug
	 * @param string $slug
	 * @return SchoolingLevel
	 */
	public static function getBySlug($slug) {
		return self::getByIndex('slug', $slug);
	}
}