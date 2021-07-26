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
use Illuminate\Support\Arr;

class Reports
{

	protected $client;

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	public function linear(string $index, string $type, string $dimension, ElasticSearchQuery $query = null, $ageRanges = null, $nullAges = null)
	{


		if ($dimension != "age") {

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
		} else {

			if ($ageRanges != null) {

				$rangeArray = [];

				foreach ($ageRanges as $ageRange) {
					$range = AgeRange::getBySlug($ageRange);

					array_push(
						$rangeArray,
						['from' => $range->from, 'to' => $range->to + 1]
					);
					//print_r($rangeArray);
				}

				if ($nullAges) {

					$request = [
						'aggs' => [
							'num_entities' => [
								'range' => [
									'field' => $dimension,
									'ranges' => $rangeArray,
								],
							],
							'num_entities_null' => [
								'missing' => ['field' => $dimension]
							]
						]
					];
				} else {

					$request = [
						'aggs' => [
							'num_entities' => [
								'range' => [
									'field' => $dimension,
									'ranges' => $rangeArray,
								],
							]
						]
					];
				}
			} else {

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


		if ($query !== null) {
			$request['query'] = $query->getQuery();
		}

		$response = $this->rawSearch([
			'index' => $index,
			'type' => $type,
			'body' => $request
		]);

		// deprecated array_pluck
		//$report = array_pluck($response['aggregations']['num_entities']['buckets'] ?? [], 'doc_count', 'key');
		//print_r($response[])
		$report = Arr::pluck($response['aggregations']['num_entities']['buckets'] ?? [], 'doc_count', 'key');
		//print_r($report);
		if ($dimension == 'age' and $nullAges) {
			array_push($report, $response['aggregations']['num_entities_null']['doc_count']);
		}

		if ($dimension == 'school_last_id' || $dimension == 'alert_cause_id') {
			if (array_key_exists('null', $report)) {
				$report['NÃO INFORMADA'] = $report['null'];
				unset($report['null']);
			}
		}

		return [
			'records_total' => $response['hits']['total'] ?? 0,
			'report' => $report
		];
	}

	public function timeline(string $index, string $type, string $dimension, ElasticSearchQuery $query = null, $ageRanges = null, $nullAges = null)
	{
		//print_r($dimension);
		if ($dimension === 'age') {

			$rangeArray = [];

			foreach ($ageRanges as $ageRange) {
				$range = AgeRange::getBySlug($ageRange);

				array_push(
					$rangeArray,
					['from' => $range->from, 'to' => $range->to + 1]
				);
			}
			$request = [
				//'size' => 0,
				"aggs" => [
					"daily" => [
						"date_histogram" => [
							"field" => "date",
							"interval" => "1D",
							"format" => "yyyy-MM-dd"
						],
						"aggs" => [
							"num_entities" => [
								"range" => [
									"field" => "age",
									'ranges' => $rangeArray,
								]
							],
							"num_entities_null" => [
								"missing" => [
									"field" => "age"
								]
							]
						]
					]
				]
			];
		} else {
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
									'field' => $dimension,
									'missing' => 'null'
								]
							]
						]
					]
				]
			];
		}

		//	echo $request;
		if ($query !== null) {
			$request['query'] = $query->getQuery();
		}
		//print_r($request);
		$response = $this->rawSearch([
			'index' => $index,
			'type' => $type,
			'body' => $request
		]);

		$report = [];

		foreach ($response['aggregations']['daily']['buckets'] as $bucket) {
			//print_r($bucket);
			if (!$bucket['num_entities']['buckets'] || sizeof($bucket['num_entities']['buckets']) <= 0) {
				$report[$bucket['key_as_string']] = null;
				continue;
			}

			$report[$bucket['key_as_string']] = array_pluck($bucket['num_entities']['buckets'], 'doc_count', 'key');
			if (array_key_exists("num_entities_null", $bucket)) {
				//$report[$bucket['key_as_string']] = array_pluck($bucket['num_entities_null'], 'doc_count', 'key');
				array_push($report[$bucket['key_as_string']], $bucket['num_entities_null']['doc_count']);
			}
		}

		//print_r($report);
		if ($dimension == 'school_last_id' || $dimension == 'alert_cause_id') {
			array_walk($report, function (&$item) {
				$item['NÃO INFORMADA'] = $item['null'];
				unset($item['null']);
			});
		}

		return [
			'records_total' => $response['hits']['total'] ?? 0,
			'report' => $report,
		];
	}

	public function buildSnapshot(CollectsDailyMetrics $entity, string $date)
	{
		$doc = $entity->buildMetricsDocument();
		$doc['date'] = $date;

		$this->rawIndex([
			'index' => $entity->getTimeSeriesIndex(),
			'type' => $entity->getTimeSeriesType(),
			'body' => $doc
		]);
	}

	public function rawSearch(array $parameters)
	{
		return $this->client->search($parameters);
	}

	public function rawIndex(array $parameters)
	{
		return $this->client->index($parameters);
	}

	public function rawDelete(array $parameters)
	{
		return $this->client->delete($parameters);
	}
}
