<?php

/**
 * busca-ativa-escolar-api
 * AlertSpawnController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 02/03/2017, 15:00
 */

namespace BuscaAtivaEscolar\Http\Controllers\Integration;


use Auth;
use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\User;

class AlertSpawnController extends BaseController
{

	public function spawn_alert()
	{

		try {

			$email = request('email', null);

			if (!$email) return response()->json(['status' => 'error', 'reason' => 'missing_user_email']);

			$user = User::where('email', $email)->first();

			if (!$user) return response()->json(['status' => 'error', 'reason' => 'invalid_user']);

			Auth::setUser($user); // Necessary so the observers fire correctly

			$tenant = $user->tenant;

			if (!$tenant) return response()->json(['status' => 'error', 'reason' => 'user_has_no_tenant']);

			$data = request()->toArray();
			$data['group_id'] =  $user->group_id;

			$validation = (new Alerta())->validate($data);

			if ($validation->fails()) return $this->api_validation_failed('validation_failed', $validation);

			$child = Child::spawnFromAlertData($tenant, $user->id, $data);

			return response()->json([
				'status' => 'ok',
				'tenant_id' => $tenant->id,
				'child_id' => $child->id,
			]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}
}
