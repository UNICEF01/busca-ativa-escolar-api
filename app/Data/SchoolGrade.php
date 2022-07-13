<?php

/**
 * busca-ativa-escolar-api
 * SchoolGrade.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 10/01/2017, 16:22
 */

namespace BuscaAtivaEscolar\Data;


class SchoolGrade extends StaticObject
{

	protected static $data = [
		5 => ['id' => 1, 'order' => 5, 'slug' => 'preescola_4anos', 'label' => "Pré-escola (4 anos)"],
		6 => ['id' => 2, 'order' => 6, 'slug' => 'preescola_5anos', 'label' => "Pré-escola (5 anos)"],
		7 => ['id' => 3, 'order' => 7, 'slug' => 'ef_1ano', 'label' => "Ensino Fundamental - Anos iniciais (1º ano)"],
		8 => ['id' => 4, 'order' => 8, 'slug' => 'ef_2ano', 'label' => "Ensino Fundamental - Anos iniciais (2º ano)"],
		9 => ['id' => 5, 'order' => 9, 'slug' => 'ef_3ano', 'label' => "Ensino Fundamental - Anos iniciais (3º ano)"],
		10 => ['id' => 6, 'order' => 10, 'slug' => 'ef_4ano', 'label' => "Ensino Fundamental - Anos iniciais (4º ano)"],
		11 => ['id' => 7, 'order' => 11, 'slug' => 'ef_5ano', 'label' => "Ensino Fundamental - Anos iniciais (5º ano)"],
		12 => ['id' => 8, 'order' => 12, 'slug' => 'ef_6ano', 'label' => "Ensino Fundamental - Anos finais (6º ano)"],
		13 => ['id' => 9, 'order' => 13, 'slug' => 'ef_7ano', 'label' => "Ensino Fundamental - Anos finais (7º ano)"],
		14 => ['id' => 10, 'order' => 14, 'slug' => 'ef_8ano', 'label' => "Ensino Fundamental - Anos finais (8º ano)"],
		15 => ['id' => 11, 'order' => 15, 'slug' => 'ef_9ano', 'label' => "Ensino Fundamental - Anos finais (9º ano)"],
		16 => ['id' => 12, 'order' => 16, 'slug' => 'em_1ano', 'label' => "Ensino Médio - 1º ano"],
		17 => ['id' => 13, 'order' => 17, 'slug' => 'em_2ano', 'label' => "Ensino Médio - 2º ano"],
		18 => ['id' => 14, 'order' => 18, 'slug' => 'em_3ano', 'label' => "Ensino Médio - 3º ano"],
		19 => ['id' => 15, 'order' => 19, 'slug' => 'eja_finais', 'label' => "EJA (ensino fundamental anos finais)"],
		20 => ['id' => 16, 'order' => 20, 'slug' => 'eja_iniciais', 'label' => "EJA (ensino fundamental anos iniciais)"],
		21 => ['id' => 17, 'order' => 21, 'slug' => 'eja_alfabetizado', 'label' => "EJA (Alfabetizado)"],
		1 => ['id' => 18, 'order' => 1, 'slug' => 'creche_1ano', 'label' => "Creche (1 ano)"],
		2 => ['id' => 19, 'order' => 2, 'slug' => 'creche_2ano', 'label' => "Creche (2 anos)"],
		3 => ['id' => 20, 'order' => 3, 'slug' => 'creche_3ano', 'label' => "Creche (3 anos)"],
		4 => ['id' => 21, 'order' => 4, 'slug' => 'creche_4ano', 'label' => "Creche (4 anos)"],
	];

	protected static $indexes = [
		'slug' => [
			'creche_1ano' => 1,
			'creche_2ano' => 2,
			'creche_3ano' => 3,
			'creche_4ano' => 4,
			'preescola_4anos' => 5,
			'preescola_5anos' => 6,
			'ef_1ano' => 7,
			'ef_2ano' => 8,
			'ef_3ano' => 9,
			'ef_4ano' => 10,
			'ef_5ano' => 11,
			'ef_6ano' => 12,
			'ef_7ano' => 13,
			'ef_8ano' => 14,
			'ef_9ano' => 15,
			'em_1ano' => 16,
			'em_2ano' => 17,
			'em_3ano' => 18,
			'eja_finais' => 19,
			'eja_iniciais' => 20,
			'eja_alfabetizado' => 21,

		]
	];

	/**
	 * @var integer The ID of the school grade
	 */
	public $id;

	/**
	 * @var string The slug of the school grade
	 */
	public $slug;

	/**
	 * @var string The human-readable name for the school grade
	 */
	public $label;

	/**
	 * @var integer The sequence order of the school grade
	 */
	public $order;

	/**
	 * Gets an school grade by it's slug
	 * @param string $slug
	 * @return SchoolGrade
	 */
	public static function getBySlug($slug)
	{
		return self::getByIndex('slug', $slug);
	}
}
