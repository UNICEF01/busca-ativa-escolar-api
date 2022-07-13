<?php
/**
 * busca-ativa-escolar-api
 * CitySearchResultsTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 27/01/2017, 16:16
 */

namespace BuscaAtivaEscolar\Transformers;


use League\Fractal\TransformerAbstract;

class CitySearchResultsTransformer extends TransformerAbstract {

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

			'name' => $document['_source']['name'] ?? null,
			'full_name' => ($document['_source']['name'] ?? null) . ' - ' . ($document['_source']['uf'] ?? null),
			'uf' => $document['_source']['uf'] ?? null,
			'region' => $document['_source']['region'] ?? null,

			'ibge_city_id' => $document['_source']['ibge_city_id'] ?? null,
			'ibge_uf_id' => $document['_source']['ibge_uf_id'] ?? null,
			'ibge_region_id' => $document['_source']['ibge_region_id'] ?? null,

			'tenant' => $document['_source']['tenant'] ?? null,

			'meta' => [
				'index' => $document['_index'] ?? 'unknown_index',
				'score' => $document['_score'] ?? 'unknown_score',
				'type' => $document['_type'] ?? 'unknown_type',
			]
		];
	}

}