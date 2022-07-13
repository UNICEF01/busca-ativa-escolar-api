<?php
/**
 * busca-ativa-escolar-api
 * VerifyPermissions.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 16/02/2017, 14:20
 */

namespace BuscaAtivaEscolar\Http\Middleware;


use Auth;
use Closure;
use Illuminate\Auth\AuthenticationException;

class VerifyPermissions {

	public function handle($request, Closure $next, ...$permissions) {

		if(!Auth::check()) {
			throw new AuthenticationException('unauthenticated');
		}

		if(!Auth::user()) throw new AuthenticationException('invalid_user');

		foreach($permissions as $permission) {
			if(!Auth::user()->can($permission)) throw new AuthenticationException('missing_permission:' . $permission);
		}

		return $next($request);
	}

}