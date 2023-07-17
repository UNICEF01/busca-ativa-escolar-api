<?php

/**
 * busca-ativa-escolar-api
 * StepsController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 08/01/2017, 01:29
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;


use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Scopes\TenantScope;
use BuscaAtivaEscolar\User;
use BuscaAtivaEscolar\Jobs\DeleteUser;

class MaintenanceController extends BaseController
{

    public function assignForAdminUser($userId)
    {
        $user = User::withoutGlobalScope(TenantScope::class)->findOrFail($userId);
        $user->lgpd = 0;
        $user->save();
        $user->delete();
        dispatch(new DeleteUser($user));
        return response()->json(['status' => 'ok', 'user' => $user]);
    }
}
