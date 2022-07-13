<?php
/**
 * busca-ativa-escolar-api
 * SMS.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 03/03/2017, 15:56
 */

namespace BuscaAtivaEscolar\SMS;

interface SmsProvider {
	public function send($number, $message);
	public function handle(\Illuminate\Http\Request $request) : SmsMessage;
}