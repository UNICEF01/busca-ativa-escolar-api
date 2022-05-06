<?php

/**
 * busca-ativa-escolar-api
 * TenantScope.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/12/2016, 21:24
 */

namespace BuscaAtivaEscolar\Scopes;


use Auth;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{

	public function apply(Builder $builder, Model $model)
	{

		if (!Auth::check()) return;

		$currentUser = Auth::user();

		// Curries regular users to their own tenants / states

		if ($currentUser->isRestrictedToUF()) {

			$builder->whereIn('tenant_id', Tenant::getIDsWithinUF($currentUser->uf));

			if ($model->getTable() === 'users') {
				$builder->orWhere(function ($query) {
					$query->whereNull('tenant_id');
					$query->whereIn('type', User::$UF_SCOPED_TYPES);
				});
			}

			return;
		}

		if ($currentUser->isRestrictedToTenant()) {
			$builder->where(function ($b) use ($currentUser, $model) {

				return $b->where("{$model->getTable()}.tenant_id", $currentUser->tenant_id)
					->orWhere('tenant_id', 'global');
			});

			return;
		}

		// When operating as a global user, URI-defined tenant is stored on TENANT_ID envvar
		// When it's != "global", it means we're scoped to a specific tenant
		// Else, it's meant to query all tenants

		if (strlen(getenv('TENANT_ID')) > 0 && getenv('TENANT_ID') != "global") {
			$builder->where('tenant_id', getenv('TENANT_ID'));
		}
	}
}
