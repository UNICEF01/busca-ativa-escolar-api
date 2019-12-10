<?php
/**
 * busca-ativa-escolar-api
 * TenantScoped.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/12/2016, 21:22
 */

namespace BuscaAtivaEscolar\Traits\Data;

use BuscaAtivaEscolar\Child;

trait checkPhases
{
    /**
     * @param $userId
     * @return \stdClass
     * Verifica a existencia de casos para o usuario informado
     */
    public static function checkIfExistsUserWithCasesInOpem($userId)
    {
        $query = self::whereHas('child', function ($query) {
            $query->where('child_status', '<>', 'cancelled');
        })->where('assigned_user_id', '=', $userId)
            ->where('is_completed', '=', 0)
            ->count('id');


        $result = new \stdClass();
        if ($query > 0) {
            $result->casos = $query;
            return $result;
        }
        $result->casos = 0;
        return $result;
    }
}