<?php
/**
 * Created by PhpStorm.
 * User: manoelfilho
 * Date: 2019-03-10
 * Time: 22:13
 */

namespace BuscaAtivaEscolar\Transformers;

use Illuminate\Database\Eloquent\Collection;
use League\Fractal\TransformerAbstract;

class EmailJobTransformer extends TransformerAbstract
{

    public function transform(Collection $emailJobs) {

        $array_collection = [];

        foreach ($emailJobs as $emailJob){

            $actual_array = [
                'type' => $emailJob->type,
                'status' => $emailJob->status,
                'user_id' => $emailJob->user_id,
                'tenant_id' => $emailJob->tenant_id,
                'school_id' => $emailJob->school_id,
                'errors' => $emailJob->errors,
                'school_email' => $emailJob->school_email,
            ];
            array_push($array_collection, $actual_array);
        }

        return $array_collection;

    }

}