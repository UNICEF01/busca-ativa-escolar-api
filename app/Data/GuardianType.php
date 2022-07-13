<?php
/**
 * busca-ativa-escolar-api
 * GuardianTypes.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 06/04/2017, 17:44
 */

namespace BuscaAtivaEscolar\Data;


class GuardianType extends StaticObject {

	protected static $data = [
		1 => ['id' => 1, 'slug' => 'father', 'label' => "Pai"],
		2 => ['id' => 2, 'slug' => 'mother', 'label' => "Mãe"],
		3 => ['id' => 3, 'slug' => 'siblings', 'label' => "Irmão/Irmã"],
		4 => ['id' => 4, 'slug' => 'other', 'label' => "Outros familiares"],
        5 => ['id' => 5, 'slug' => 'grandparents', 'label' => "Avós"],
	];

	protected static $indexes = [
		'slug' => [
			'father' => 1,
			'mother' => 2,
			'siblings' => 3,
			'other' => 4,
            'grandparents' => 5
		]
	];

	/**
	 * @var integer The ID of the guardian type
	 */
	public $id;

	/**
	 * @var string The slug of the guardian type
	 */
	public $slug;

	/**
	 * @var string The human-readable name for the guardian type
	 */
	public $label;

	/**
	 * @var integer The sequence order of the guardian type
	 */
	public $order;

	/**
	 * Gets a guardian type by it's slug
	 * @param string $slug
	 * @return GuardianType
	 */
	public static function getBySlug($slug) {
		return self::getByIndex('slug', $slug);
	}

}