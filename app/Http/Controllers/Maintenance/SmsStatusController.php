<?php
/**
 * busca-ativa-escolar-api
 * SmsStatusController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 24/04/2017, 19:15
 */

namespace BuscaAtivaEscolar\Http\Controllers\Maintenance;


use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\SmsConversation;

class SmsStatusController extends BaseController {
	
	public function get_conversations() {
		$conversations = SmsConversation::query()->orderBy('created_at')->take(32)->get();
		return response()->json(['status' => 'ok', 'data' => $conversations]);
	}

}