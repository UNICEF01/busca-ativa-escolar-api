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


use BuscaAtivaEscolar\StateSignup;
use League\Fractal\TransformerAbstract;

class StateTransformer extends TransformerAbstract {

	protected $defaultIncludes = [
		'admin',
		'coordinator',
	];

	protected $availableIncludes = [
		'users',
		'admin',
		'coordinator',
	];

	public function transform(StateSignup $state) {

		return [
			'id' => $state->id,
			'uf' => $state->uf,

			'admin_id' => $state->admin_id,
			'coordinator_id' => $state->coordinator_id,

			'is_approved' => $state->is_approved,

			'created_at' => $state->created_at ? $state->created_at->toIso8601String() : null,
			'updated_at' => $state->updated_at ? $state->updated_at->toIso8601String() : null,
		];

	}

	public function includeAdmin(StateSignup $state) {
		if(!$state->admin) return null;
		return $this->item($state->admin, new UserTransformer(), false);
	}

	public function includeCoordinator(StateSignup $state) {
		if(!$state->coordinator) return null;
		return $this->item($state->coordinator, new UserTransformer(), false);
	}

	public function includeUsers(StateSignup $state) {
		if(!$state->users) return null;
		return $this->collection($state->users, new UserTransformer(), false);
	}

}