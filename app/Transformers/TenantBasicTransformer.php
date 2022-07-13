<?php
/**
 * busca-ativa-escolar-api
 * TenantBasicTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2018
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 14/03/2017, 18:02
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\Tenant;
use League\Fractal\TransformerAbstract;

class TenantBasicTransformer extends TransformerAbstract {

	public function transform(Tenant $tenant) {

		return [
			'id' => $tenant->id,
			'name' => $tenant->name,

			'city_id' => $tenant->city_id,
			'uf' => $tenant->uf,
		];

	}

}