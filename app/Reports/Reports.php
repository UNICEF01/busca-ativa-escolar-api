<?php
/**
 * busca-ativa-escolar-api
 * Reports.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 01/02/2017, 17:25
 */

namespace BuscaAtivaEscolar\Reports;


use BuscaAtivaEscolar\Data\AgeRange;
use BuscaAtivaEscolar\Reports\Interfaces\CanBeAggregated;
use BuscaAtivaEscolar\Reports\Interfaces\CollectsDailyMetrics;
use BuscaAtivaEscolar\Search\ElasticSearchQuery;
use Elasticsearch\Client;

class Reports {

	protected $client;

	public function __construct(Client $client) {
		$this->client = $client;
	}

	public function linear(string $index, string $type, string $dimension, ElasticSearchQuery $query = null, $ageRanges = null) {

         /*
          * Validate of dimension for age!
          * When there is age dimension, we can't agregate INTEGER and NULL values. Only with number.
          * The difference of total and elements grouped is calculated in ReportsController
          */
         if( $dimension != "age"){

             $request = [
                 'size' => 0,
                 'aggs' => [
                     'num_entities' => [
                         'terms' => [
                             'size' => 0,
                             'field' => $dimension,
                             'missing' => 'null'
                         ]
                     ]
                 ]
             ];

         }else{

             if( $ageRanges != null ){

                 $rangeArray = [];

                 foreach ($ageRanges as $ageRange) {
                     $range = AgeRange::getBySlug($ageRange);
                     array_push(
                         $rangeArray,
                         ['from' => $range->from, 'to' => $range->to]
                     );
                 }

                 $request = [
                     'aggs' => [
                         'num_entities' => [
                             'range' => [
                                 'field' => $dimension,
                                 'ranges' => $rangeArray
                             ],
                         ]
                     ]
                 ];

             }else{

                 $request = [
                     'aggs' => [
                         'num_entities' => [
                             'terms' => [
                                 'size' => 0,
                                 'field' => $dimension
                             ]
                         ]
                     ]
                 ];

             }
         }


		if($query !== null) {
			$request['query'] = $query->getQuery();
		}

		$response = $this->rawSearch([
			'index' => $index,
			'type' => $type,
			'body' => $request
		]);

		return [
			'records_total' => $response['hits']['total'] ?? 0,
			'report' => array_pluck($response['aggregations']['num_entities']['buckets'] ?? [], 'doc_count', 'key')
		];

	}

	public function timeline(string $index, string $type, string $dimension, ElasticSearchQuery $query = null) {

		$request = [
			'size' => 0,
			'aggs' => [
				'daily' => [
					'date_histogram' => [
						'field' => 'date',
						'interval' => '1D',
						'format' => 'yyyy-MM-dd'
					],
					'aggs' => [
						'num_entities' => [
							'terms' => [
								'size' => 0,
								'field' => $dimension
							]
						]
					]
				]
			]
		];

		if($query !== null) {
			$request['query'] = $query->getQuery();
		}

		$response = $this->rawSearch([
			'index' => $index,
			'type' => $type,
			'body' => $request
		]);

		$report = [];

		foreach($response['aggregations']['daily']['buckets'] as $bucket) {

			if(!$bucket['num_entities']['buckets'] || sizeof($bucket['num_entities']['buckets']) <= 0) {
				$report[$bucket['key_as_string']] = null;
				continue;
			}

			$report[$bucket['key_as_string']] = array_pluck($bucket['num_entities']['buckets'], 'doc_count', 'key');
		}

		return [
			'records_total' => $response['hits']['total'] ?? 0,
			'report' => $report,
		];

	}

	public function buildSnapshot(CollectsDailyMetrics $entity, string $date) {
		$doc = $entity->buildMetricsDocument();
		$doc['date'] = $date;

		$this->rawIndex([
			'index' => $entity->getTimeSeriesIndex(),
			'type' => $entity->getTimeSeriesType(),
			'body' => $doc
		]);
	}

	public function rawSearch(array $parameters) {
		return $this->client->search($parameters);
	}

	public function rawIndex(array $parameters) {
		return $this->client->index($parameters);
	}

	public function rawDelete(array $parameters) {
		return $this->client->delete($parameters);
	}

}