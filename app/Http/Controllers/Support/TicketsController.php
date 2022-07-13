<?php
/**
 * busca-ativa-escolar-api
 * TicketsController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 14/07/2017, 16:03
 */

namespace BuscaAtivaEscolar\Http\Controllers\Support;


use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Mailables\NewSupportTicket;
use BuscaAtivaEscolar\SupportTicket;
use JWTAuth;
use Mail;

class TicketsController extends BaseController {

	public function submit_ticket() {

		try {

			$ticket = new SupportTicket();
			$ticket->fill(request()->all());
			$ticket->user_agent = request()->header('User-Agent');

			try {
				$user = JWTAuth::parseToken()->authenticate();

				if($user) {
					$ticket->user_id = $user->id;
					$ticket->tenant_id = $user->tenant_id;
				}
			} catch (\Exception $ex) {}


			$ticket->save();

			$message = new NewSupportTicket($ticket);
			$targets = explode(",", env('SUPPORT_TICKET_TARGETS', 'dev@lqdi.net'));

			Mail::to($targets)->send($message);

			return $this->api_success();

		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}

	}

}