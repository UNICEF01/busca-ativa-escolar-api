<?php
/**
 * busca-ativa-escolar-api
 * SchoolSearchResultsTransformer.php
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

class SchoolSearchResultsTransformer extends TransformerAbstract {

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

			'uf' => $document['_source']['uf'] ?? null,
			'region' => $document['_source']['region'] ?? null,

			'city_ibge_id' => $document['_source']['city_ibge_id'] ?? null,
			'city_id' => $document['_source']['city_id'] ?? null,
			'city_name' => $document['_source']['city_name'] ?? null,

			'meta' => [
				'index' => $document['_index'] ?? 'unknown_index',
				'score' => $document['_score'] ?? 'unknown_score',
				'type' => $document['_type'] ?? 'unknown_type',
			]
		];
	}

}