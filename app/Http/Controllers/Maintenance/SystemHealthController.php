<?php
/**
 * busca-ativa-escolar-api
 * SystemHealthController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 28/04/2017, 12:02
 */

namespace BuscaAtivaEscolar\Http\Controllers\Maintenance;


use BuscaAtivaEscolar\Http\Controllers\BaseController;
use Log;

class SystemHealthController extends BaseController {

	public function get_health() {

		$clusterHealthURL = 'http://' . env('ELASTICSEARCH_ADDR') . '/_cluster/health';
		$nodesHealthURL = 'http://' . env('ELASTICSEARCH_ADDR') . '/_nodes/stats/os';

		try {
			$clusterHealth = json_decode(file_get_contents($clusterHealthURL), true);
			$nodesHealth = json_decode(file_get_contents($nodesHealthURL), true);
			$primaryNodeHealth = array_pop($nodesHealth['nodes']);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}

		return response()->json([
			'status' => 'ok',
			'search' => [
				'health' => $clusterHealth,
				'os' => $primaryNodeHealth['os']
			]
		]);
	}

	public function test_error_reporting() {
		Log::info("This is a logged info");
		Log::error("This is a logged error");
		throw new \Exception("This is a test exception");
	}


}