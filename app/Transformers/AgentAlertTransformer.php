<?php
/**
 * busca-ativa-escolar-api
 * PendingAlertTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 09/02/2017, 20:21
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\Child;
use League\Fractal\TransformerAbstract;

class AgentAlertTransformer extends TransformerAbstract {

	public function transform(Child $child) {
		return [
			'id' => $child->id,
			'name' => $child->name,

			'tenant_id' => $child->tenant_id,

			'current_step' => $child->currentStep ? $child->currentStep->getName() : '---',

			'deadline_status' => $child->deadline_status,
			'alert_status' => $child->alert_status,

			'created_at' => $child->created_at ? $child->created_at->toIso8601String() : null,
		];
	}

}