<?php
/**
 * busca-ativa-escolar-api
 * CityTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 18/01/2017, 18:35
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\City;
use League\Fractal\TransformerAbstract;

class CityTransformer extends TransformerAbstract {

	public function transform(City $city) {

		return [
			'id' => $city->id,

			'uf' => $city->uf,
			'region' => $city->region,

			'name' => $city->name,

			'ibge_city_id' => $city->ibge_city_id,
			'ibge_uf_id' => $city->ibge_uf_id,
			'ibge_region_id' => $city->ibge_region_id,

			'webdoc_url' => $city->webdoc_url,

		];

	}

}