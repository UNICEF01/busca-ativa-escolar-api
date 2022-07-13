<?php
/**
 * busca-ativa-escolar-api
 * ImportJobTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2018
 *
 * @author Aryel Tupinamba <aryel.tupinamba@lqdi.net>
 *
 * Created at: 14/03/2018, 17:57
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\ImportJob;
use League\Fractal\TransformerAbstract;

class ImportJobTransformer extends TransformerAbstract {

	protected $availableIncludes = [
		'user',
		'tenant',
	];

	protected $defaultIncludes = [
		'user',
		'tenant'
	];

	public function transform(ImportJob $job) {
		return [
			'id' => $job->id,
			'created_at' => $job->created_at ? $job->created_at->format('Y-m-d H:i:s') : null,
			'type' => $job->type,
			'status' => $job->status,
			'user_id' => $job->user_id,
			'tenant_id' => $job->tenant_id,
			'path' => $job->path,
			'offset' => $job->offset,
			'total_records' => $job->total_records,
            'duplicateds' => $job->duplicateds,
            'first_child' => $job->first_child,
            'last_child' => $job->last_child,
            'errors' => $job->errors ? $job->errors : []
		];
	}

	public function includeUser(ImportJob $job) {
		if(!$job->user) return null;
		return $this->item($job->user, new UserTransformer(), false);
	}

	public function includeTenant(ImportJob $job) {
		if(!$job->tenant) return null;
		return $this->item($job->tenant, new TenantBasicTransformer(), false);
	}

}