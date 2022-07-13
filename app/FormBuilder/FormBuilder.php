<?php
/**
 * busca-ativa-escolar-api
 * FormBuilder.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 06/04/2017, 17:04
 */

namespace BuscaAtivaEscolar\FormBuilder;


class FormBuilder {

	protected $fields = [];
	protected $groups = [];

	public function field(string $fieldName, string $fieldType, string $fieldLabel, array $fieldOptions = []) : FormBuilder {
		$this->fields[$fieldName] = [
			'name' => $fieldName,
			'type' => $fieldType,
			'label' => $fieldLabel,
			'options' => $fieldOptions
		];

		return $this;
	}

	public function group(string $groupName, string $groupLabel, callable $group) : FormBuilder {

		if($groupName === 'root') throw new \InvalidArgumentException("Cannot use 'root' as a group name");

		$builder = $group(new FormBuilder()); /* @var $builder FormBuilder */

		$this->groups[$groupName] = ['name' => $groupName, 'label' => $groupLabel, 'fields' => $builder->getFields()];

		return $this;
	}

	public function getFields() : array {
		return $this->fields;
	}

	public function buildTree() : array {
		$data = [
			'root' => [
				'is_root' => true,
				'name' => 'root',
				'label' => '',
				'fields' => $this->getFields(),
			],
		];

		foreach($this->groups as $groupName => $group) {
			$data[$groupName] = $group;
		}

		return $data;
	}

}