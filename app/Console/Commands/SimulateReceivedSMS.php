<?php
/**
 * busca-ativa-escolar-api
 * SimulateReceivedSMS.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 03/03/2017, 14:23
 */

namespace BuscaAtivaEscolar\Console\Commands;


use Curl;
use Request;
use Route;

class SimulateReceivedSMS extends Command {

	protected $signature = 'debug:simulate_received_sms {number} {message}';
	protected $description = 'Simulates a received SMS';

	public function handle() {

		$number = $this->argument('number');
		$message = $this->argument('message');

		try {
			$response = Curl::to(url('api/v1/integration/sms/on_receive'))
				->asJsonRequest()
				->withData([
					'callbackMoRequest' => [
						'id' => strval(time()),
						'body' => $message,
						'mobile' => $number,
						'account' => env('ZENVIA_USER'),
						'received' => date("T-m-d\TH:i:s"),
						'shortCode' => env('ZENVIA_SHORTCODE'),
						'mobileOperatorName' => 'Claro'
					]
				])
				->post();

			dd($response);
		} catch (\Exception $ex) {
			dd($ex);
		}


	}

}