<?php

/**
 * busca-ativa-escolar-api
 * SettingsController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 13/02/2017, 18:50
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;


use Auth;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Serializers\SimpleArraySerializer;
use BuscaAtivaEscolar\Transformers\TenantSettingsTransformer;

class SettingsController extends BaseController
{

	public function get_tenant_settings()
	{

		$user = Auth::user();

		if (!$user->isRestrictedToTenant()) {
			return response()->json(['status' => 'failed', 'reason' => 'user_has_no_tenant']);
		}

		$settings = $user->tenant->getSettings();  /* @var $settings TenantSettings */

		return fractal()
			->item($settings)
			->transformWith(new TenantSettingsTransformer($user->tenant))
			->serializeWith(new SimpleArraySerializer())
			->respond();
	}

	public function update_tenant_settings()
	{
		$user = Auth::user();

		// TODO: validate if user can change settings

		if (!$user->isRestrictedToTenant()) {
			return response()->json(['status' => 'failed', 'reason' => 'user_has_no_tenant']);
		}

		$settings = $user->tenant->getSettings(); /* @var $settings TenantSettings */
		$settings->update(request()->all());

		$user->tenant->setSettings($settings);

		return response()->json(['status' => 'ok']);
	}


	public function get_tenant_settings_of_case($id)
	{

		$tenant = \BuscaAtivaEscolar\Tenant::where('id', $id)->first();
		$settings = $tenant->getSettings();

		return fractal()
			->item($settings)
			->transformWith(new TenantSettingsTransformer($tenant))
			->serializeWith(new SimpleArraySerializer())
			->respond();
	}
}
