<?php
/**
 * Created by PhpStorm.
 * User: mansouza
 * Date: 3/22/2019
 * Time: 12:36 PM
 */

namespace BuscaAtivaEscolar\Transformers;


use League\Fractal\TransformerAbstract;

class SchoolCustomTransformer extends TransformerAbstract
{

    public function transform(\stdClass $school) {
        return [
            'id' => $school->id,
            'name' => $school->name,
            'city_name' => $school->city_name,
            'school_cell_phone' => $school->school_cell_phone,
            'school_phone' => $school->school_phone,
            'school_email' => $school->school_email,
            'count_children' => $school->count_children,
            'count_with_cep' => $school->count_with_cep,
        ];
    }

}