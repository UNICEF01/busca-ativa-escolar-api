<?php
/**
 * busca-ativa-escolar-api
 * ChildSearchTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/01/2017, 23:58
 */

namespace BuscaAtivaEscolar\Transformers;


use League\Fractal\TransformerAbstract;

class SearchResultsTransformer extends TransformerAbstract {

	protected $availableIncludes = [
		'results',
	];

	protected $defaultIncludes = [
		'results',
	];

	protected $resultsTransformer;
	protected $query;
	protected $attempted;

	public function __construct($resultsTransformer, array $query, $attempted = null) {
		$this->resultsTransformer = $resultsTransformer;
		$this->query = $query;
		$this->attempted = $attempted;

		if(!$this->resultsTransformer) throw new \Exception("Invalid transformer class: {$this->resultsTransformer}");
	}

	public function transform($result) {

		if(!isset($result['hits'])) {
			return [
				'status' => 'failed',
				'response' => $result,
				//'query' => $this->query,
			];
		}

		return [
			'status' => 'ok',
			//'raw' => config('app.debug', false) ? $result : null,
			'stats' => [
				'total_results' => $result['hits']['total'] ?? 0,
				'max_score' => $result['hits']['max_score'] ?? 0,
				'query_time' => $result['took'] ?? 0,
				'shards' => $result['_shards'] ?? [],
			],
			'query' => $this->query,
			'attempted' => $this->attempted,
		];
	}

	public function includeResults($result) {
		if(!isset($result['hits'])) return null;
		if(!isset($result['hits']['hits'])) return null;
		return $this->collection($result['hits']['hits'], new $this->resultsTransformer, false);
	}

}