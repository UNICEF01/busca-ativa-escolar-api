<?php
/**
 * busca-ativa-escolar-api
 * ChildSearchResultsTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 23/01/2017, 24:02
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\Child;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ChildSearchResultsTransformer extends TransformerAbstract {

	public function transform($document) {

		if(!isset($document['_source'])) {
			return [
				'status' => 'failed',
				'id' => $document['_id'] ?? 'unknown_id',
				'meta' => [
					'index' => $document['_index'] ?? 'unknown_index',
					'error' => 'null_source_document',
				]
			];
		}

		return [
			'status' => 'ok',
			'id' => $document['_id'],
			'name' => $document['_source']['name'] ?? '',

			'tenant_id' => $document['_source']['tenant_id'] ?? null,

			'mother_name' => $document['_source']['mother_name'] ?? null,
			'father_name' => $document['_source']['father_name'] ?? null,
			
			'place_city_name' => $document['_source']['place_city_name'] ?? null,
			'place_uf' => $document['_source']['place_uf'] ?? null,	


			
			'risk_level' => $document['_source']['risk_level'] ?? null,
			'gender' => $document['_source']['gender'] ?? null,
			'age' => $document['_source']['age'] ?? null,

			'assigned_user_id' => $document['_source']['assigned_user_id'] ?? null,
			'assigned_user_name' => $document['_source']['assigned_user_name'] ?? null,
			'assigned_group_name' => $document['_source']['assigned_group_name'] ?? null,

			'current_case_id' => $document['_source']['current_case_id'] ?? null,

			'step_name' => $document['_source']['step_name'] ?? null,
			'current_step_type' => $document['_source']['current_step_type'] ?? null,
			'current_step_id' => $document['_source']['current_step_id'] ?? null,

			'current_case' => $document['_source']['current_case'] ?? null,

			'is_late' => (($document['_source']['deadline_status'] ?? 'normal') === Child::DEADLINE_STATUS_LATE),

			'child_status' => $document['_source']['child_status'] ?? null,
			'case_status' => $document['_source']['case_status'] ?? null,
			'deadline_status' => $document['_source']['deadline_status'] ?? null,
			'alert_status' => $document['_source']['alert_status'] ?? null,

            'cancel_reason' => $document['_source']['cancel_reason'] ?? null,

			'created_at' => isset($document['_source']['created_at']) ? Carbon::createFromTimestamp(strtotime($document['_source']['created_at']))->toIso8601String() : null,
			'updated_at' => isset($document['_source']['updated_at']) ? Carbon::createFromTimestamp(strtotime($document['_source']['updated_at']))->toIso8601String() : null,

			'meta' => [
				'index' => $document['_index'] ?? 'unknown_index',
				'score' => $document['_score'] ?? 'unknown_score',
				'type' => $document['_type'] ?? 'unknown_type',
			],

            'place_address' => $document['_source']['place_address'] ?? null,
            'place_neighborhood' => $document['_source']['place_neighborhood'] ?? null,
            'place_cep' => $document['_source']['place_cep'] ?? null,
			'goup_name' =>  $document['_source']['group_name'] ?? null,
            'dob' => $document['_source']['dob'] ?? null,
		];
	}

}