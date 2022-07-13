<?php
/**
 * busca-ativa-escolar-api
 * ZenviaHandler.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 03/03/2017, 16:00
 */

namespace BuscaAtivaEscolar\SMS\Handlers;


use BuscaAtivaEscolar\SMS\SmsMessage;
use BuscaAtivaEscolar\SMS\SmsProvider;
use Curl;
use Log;
use Webpatser\Uuid\Uuid;

class Zenvia implements SmsProvider {

	protected function getAuthHeader() {
		return base64_encode(env('ZENVIA_USER') . ':' . env('ZENVIA_KEY'));
	}

	public function send($number, $message) {

		$headers = [
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: ' . $this->getAuthHeader()
		];

		$data = [
			'sendSmsRequest' => [
				'to' => $number,
				'msg' => $message,
				'callbackOption' => 'NONE',
				'id' => Uuid::generate()->string,
				'aggregateId' => env('ZENVIA_AGGREGATE_ID')
			]
		];

		Log::debug("[sms_handler.zenvia] Sending - data=" . json_encode($data) . ", headers=" . json_encode($headers));

		$response = Curl::to('https://api-rest.zenvia360.com.br/services/send-sms')
			->asJsonRequest()
			->withHeaders($headers)
			->withData($data)
			->enableDebug(storage_path('logs/curl_zenvia.log'))
			->post();

		Log::debug("[sms_handler.zenvia] Received response: " . json_encode($response));

		return true;

	}

	public function handle(\Illuminate\Http\Request $request): SmsMessage {
		$id = $request['callbackMoRequest']['id'] ?? null;
		$number = $request['callbackMoRequest']['mobile'] ?? null;
		$message = $request['callbackMoRequest']['body'] ?? null;
		$shortCode = $request['callbackMoRequest']['shortCode'] ?? null;
		$carrier = $request['callbackMoRequest']['mobileOperatorName'] ?? null;

		if(!is_string($number) || sizeof($number) <= 0) {
			throw new \InvalidArgumentException("Invalid MSISDN in request: " . json_encode($request));
		}

		if(!is_string($message) || sizeof($message) <= 0) {
			$message = "";
		}

		$keyword = env('ZENVIA_KEYWORD');

		// Strips the keyword from the message
		if(strtolower(substr(trim($message), 0, strlen($keyword))) == strtolower($keyword)) {
			$message = trim(substr(trim($message), strlen($keyword)));
		}

		return new SmsMessage($id, $number, $message, $shortCode, $carrier);
	}


}