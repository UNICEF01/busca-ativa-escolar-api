<?php
/**
 * busca-ativa-escolar-api
 * BaseController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/12/2016, 21:01
 */

namespace BuscaAtivaEscolar\Http\Controllers;

use BuscaAtivaEscolar\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Contracts\Validation\Validator;
use Log;

class BaseController extends Controller {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/**
	 * Gets the currently authenticated user
	 * @return User|null
	 */
    protected function currentUser() {
    	if(auth()->guest()) return null;
    	return auth()->user();
    }

    protected function tickTenantLastActivity() {
    	if(!auth()->check()) return;
    	if(!auth()->user()->tenant) return;

	    try {
		    auth()->user()->tenant->tickLastActivity();
	    } catch (\Exception $ex) {
	    	Log::debug('[tick_tenant_last_activity] ' . $ex->getMessage());
	    }
    }

    protected $_debug = [];

    protected function debug_push(... $params) {
        ob_start();

        foreach($params as $param) {
            dump($param);
        }

        $content = ob_get_contents();

        ob_end_clean();

        array_push($this->_debug, $content);
    }

    protected function debug_output() {
        return response(join("<br><br>", $this->_debug));
    }

    protected function api_exception(\Exception $exception, $data = []) {

    	if(!$data) $data = [];

    	Log::error('[api_exception] ' . $exception->getMessage() . "\n\n {$exception->getTraceAsString()}");

	    $exceptionInfo = $exception->getMessage();

	    if(env('APP_DEBUG', false)) {
	    	$exceptionInfo = [
			    'message' => $exception->getMessage(),
			    'stack' => $exception->getTrace()
		    ];
	    }

	    $data['status'] = 'error';
	    $data['reason'] = 'exception';
	    $data['exception'] = $exceptionInfo;

    	return response()->json($data, 500);
    }

	protected function api_validation_failed($reason, Validator $validator, $data = [])
	{
		if (!$data) $data = [];

		$data['status'] = 'error';
		$data['reason'] = $reason;
		$data['messages'] = $validator->getMessageBag()->all();

		return response()->json($data, 422); // Código HTTP 422 Unprocessable Entity para falhas de validação
	}


	protected function api_failure($reason, $fields = null, $data = []) {
    	if(!$data) $data = [];

    	Log::debug("[api_failure] Returned API failure: {$reason}; fields=" . json_encode($fields) . ", data=" .json_encode($data));

    	$data['status'] = 'error';
		$data['reason'] = $reason;

		if($fields) $data['fields'] = $fields;

		return response()->json($data);
	}

	protected function api_success($data = []) {
    	if(!$data) $data = [];

    	$data['status'] = 'ok';

    	return response()->json($data);
	}
}
