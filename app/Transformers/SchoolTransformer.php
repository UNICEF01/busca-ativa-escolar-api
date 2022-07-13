<?php
/**
 * busca-ativa-escolar-api
 * SchoolTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 10/02/2017, 15:49
 */

namespace BuscaAtivaEscolar\Transformers;

use BuscaAtivaEscolar\School;
use League\Fractal\TransformerAbstract;
use BuscaAtivaEscolar\Transformers\EmailJobTransformer;

class SchoolTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'emailJobs',
    ];

    protected $defaultIncludes = [
        'emailJobs',
    ];

    public function transform(School $school) {
		return [
			'id' => $school->id,
			'name' => $school->name,
			'uf' => $school->uf,
			'region' => $school->region,
			'city_ibge_id' => $school->city_ibge_id,
			'city_id' => $school->city_id,
			'city_name' => $school->city_name,
            'school_cell_phone' => $school->school_cell_phone,
            'school_phone' => $school->school_phone,
            'school_email' => $school->school_email,
            'periodicidade' => $school->periodicidade
		];
	}

    public function includeEmailJobs(School $school) {
        if(!$school->emailJobs) return null;
        return $this->item($school->emailJobs, new EmailJobTransformer(), false);
    }

}