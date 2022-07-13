<?php
/**
 * busca-ativa-escolar-api
 * GroupTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 23/01/2017, 18:27
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\Group;
use League\Fractal\TransformerAbstract;

class GroupTransformer extends TransformerAbstract {

	public $availableIncludes = [
		'settings',
		'tenant',
	];

	public function transform(Group $group) {
		return [
			'id' => $group->id,
			'name' => $group->name,
			'is_primary' => $group->is_primary,
			'tenant_id' => $group->tenant_id,
			'created_at' => $group->created_at ? $group->created_at->toIso8601String() : null,
			'updated_at' => $group->updated_at ? $group->updated_at->toIso8601String() : null
		];
	}

	public function includeTenant(Group $group) {
		if(!$group->tenant) return null;
		return $this->item($group->tenant, new TenantTransformer(), false);
	}

	public function includeSettings(Group $group) {
		$settings = $group->getSettings();
		if(!$settings) return null;
		return $this->item($settings, new GenericTransformer(), false);
	}

}