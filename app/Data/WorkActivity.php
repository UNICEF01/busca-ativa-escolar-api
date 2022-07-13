<?php
/**
 * busca-ativa-escolar-api
 * WorkActivity.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 10/01/2017, 16:45
 */

namespace BuscaAtivaEscolar\Data;


class WorkActivity extends StaticObject {

	protected static $data = [
		1 => ['id' => 1, 'slug' => 'servico_domestico', 'label' => "Serviço doméstico"],
		2 => ['id' => 2, 'slug' => 'negocio_familiar', 'label' => "Negócio familiar"],
		3 => ['id' => 3, 'slug' => 'other', 'label' => "Outra atividade"],
    ];

	protected static $indexes = [
		'slug' => [
			'servico_domestico' => 1,
			'negocio_familiar' => 2,
			'other' => 3,
		]
	];

	/**
	 * @var string The activity ID
	 */
	public $id;

	/**
	 * @var string The activity slug
	 */
	public $slug;

	/**
	 * @var string The activity human-readable name
	 */
	public $label;

	/**
	 * Gets a work activity by its slug
	 * @param string $slug
	 * @return WorkActivity
	 */
	public static function getBySlug($slug) {
		return self::getByIndex('slug', $slug);
	}

}