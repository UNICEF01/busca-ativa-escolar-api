<?php
/**
 * busca-ativa-escolar-api
 * PreferencesController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 09/03/2017, 17:54
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;


use Auth;
use BuscaAtivaEscolar\Http\Controllers\BaseController;

class PreferencesController extends BaseController {

	public function updateSettings() {
		$user = Auth::user();

		$settings = $user->getSettings();
		$settings->update( request('settings', []) );
		$user->setSettings($settings);

		return response()->json(['status' => 'ok']);
	}

	public function getSettings() {
		$user = Auth::user();
		$settings = $user->getSettings();

		return response()->json(['status' => 'ok', 'settings' => $settings]);
	}

}