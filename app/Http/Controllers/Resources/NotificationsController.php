<?php

/**
 * busca-ativa-escolar-api
 * NotificationsController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 09/03/2017, 14:53
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;


use Auth;
use BuscaAtivaEscolar\Http\Controllers\BaseController;

class NotificationsController extends BaseController
{

	public function getUnread()
	{
		return response()->json(['data' => []]);
	}

	public function markAsRead($id)
	{
		$user = Auth::user(); /* @var $user User */
		$notification = $user->notifications()->where('id', $id)->first();

		if (!$notification) return $this->api_failure('invalid_notification');

		$notification->markAsRead();

		return response()->json(['status' => 'ok']);
	}
}
