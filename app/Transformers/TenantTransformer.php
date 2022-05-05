<?php
/**
 * busca-ativa-escolar-api
 * TenantTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 18/01/2017, 18:33
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\Tenant;
use League\Fractal\TransformerAbstract;

class TenantTransformer extends TransformerAbstract {

	protected $defaultIncludes = [
		'city'
	];

	protected $availableIncludes = [
		'city',
        'users',
        'settings',
		'political_admin',
		'operational_admin',
	];

	public function transform(Tenant $tenant) {

		$daysSinceLastActive = $this->getDaysSinceLastActive($tenant->last_active_at);

		return [
			'id' => $tenant->id,
            'deleted_at' => $tenant->deleted_at ? $tenant->deleted_at : null,
            'name' => $tenant->name,

			'city_id' => $tenant->city_id,
			'uf' => $tenant->uf,
			'operational_admin_id' => $tenant->operational_admin_id,
			'political_admin_id' => $tenant->political_admin_id,

			'is_registered' => $tenant->is_registered,
			'is_active' => $tenant->is_active,
			'is_setup' => $tenant->is_setup,

			'last_active_at' => $tenant->last_active_at ? $tenant->last_active_at->toIso8601String() : null,
			'days_since_last_active' => $daysSinceLastActive,
			'activity_status' => $this->resolveActivityStatus($daysSinceLastActive),

			'registered_at' => $tenant->registered_at ? $tenant->registered_at->toIso8601String() : null,
			'activated_at' => $tenant->activated_at ? $tenant->activated_at->toIso8601String() : null,

            'is_state' => $tenant->is_state,

            'primary_group_id' => $tenant->primary_group_id,
            'primary_group_name' => $tenant->primaryGroup ? $tenant->primaryGroup->name : null

		];

	}

	protected function getDaysSinceLastActive($lastActiveAt) {
		if(!$lastActiveAt) return null;
		return abs($lastActiveAt->diffInDays());
	}

	protected function resolveActivityStatus($daysSinceLastActive) {
		if($daysSinceLastActive === null) return 'inactive_never';

		if($daysSinceLastActive >= 120) return 'inactive_120d';
		if($daysSinceLastActive >= 90) return 'inactive_90d';
		if($daysSinceLastActive >= 60) return 'inactive_60d';
		if($daysSinceLastActive >= 30) return 'inactive_30d';

		return 'active';
	}

	public function includeCity(Tenant $tenant) {
		return $this->item($tenant->city, new CityTransformer(), false);
	}

	public function includePoliticalAdmin(Tenant $tenant) {
		if(!$tenant->politicalAdmin) return null;
		return $this->item($tenant->politicalAdmin, new UserTransformer(), false);
	}

	public function includeOperationalAdmin(Tenant $tenant) {
		if(!$tenant->operationalAdmin) return null;
		return $this->item($tenant->operationalAdmin, new UserTransformer(), false);
	}

	public function includeSettings(Tenant $tenant) {
		$settings = $tenant->getSettings();
		if(!$settings) return null;
		return $this->item($settings, new GenericTransformer(), false);
	}

    public function includeUsers(Tenant $tenant) {
        $users = $tenant->users();
        if(!$users) return null;
        return $this->item($users, new ListUsersTransformer(), false);
    }

}