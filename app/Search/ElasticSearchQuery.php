<?php

/**
 * busca-ativa-escolar-api
 * SearchQuery.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 23/01/2017, 14:19
 */

namespace BuscaAtivaEscolar\Search;


class ElasticSearchQuery
{

	/**
	 * @var array The given search parameters.
	 */
	protected $params;

	/**
	 * @var array The ElasticSearch query being built.
	 */
	protected $query = [
		'bool' => [
			'must' => [],
			'should' => [],
			'filter' => []
		]
	];

	protected $attemptedQuery = [];

	/**
	 * SearchQuery constructor.
	 * Creates a search query and gives it a specified set of search parameters.
	 * You might want to use the static method SearchQuery::withParameters() instead, for fluid composition.
	 *
	 * @param array $params The search parameters (associative array of fields to search and expected values).
	 */
	public function __construct(array $params)
	{
		$this->params = $params;
	}

	/**
	 * Adds a single text field to the list of fields to be searched on.
	 * Will search for the $params[$name] value in the $name field in the documents.
	 * Adds a 'term' search.
	 *
	 * @param string $name The name of the field in both search parameters and document.
	 * @param string $mode The search mode ('term' for full term, or 'match' for loose query)
	 * @param string $priority The 'priority' to use (must/should/filter). See ElasticSearch docs for more info.
	 * @return self Same query instance, for fluid composition.
	 */
	public function addTextField(string $name, string $mode = 'term', string $priority = 'must'): self
	{
		array_push($this->attemptedQuery, ['addTextField', $name, $priority, $this->params[$name] ?? null]);

		if (!isset($this->params[$name]))
			return $this;
		if (strlen($this->params[$name]) <= 0)
			return $this;

		array_push($this->query['bool'][$priority], [$mode => [$name => $this->params[$name]]]);

		return $this;
	}

	/**
	 * Adds multiple text fields to the list of fields to be searched on.
	 * Each field will be searched for the $params[$name] value in the $name field in the documents.
	 * Adds a 'term' search.
	 *
	 * @param array $names List of names of the fields in both search parameters and document.
	 * @param string $mode The search mode ('term' for full term, or 'match' for loose query)
	 * @param string $priority The 'priority' to use (must/should/filter). See ElasticSearch docs for more info.
	 * @return self Same query instance, for fluid composition.
	 */
	public function addTextFields(array $names, string $mode = 'term', string $priority = 'must'): self
	{
		array_push($this->attemptedQuery, ['addTextFields+', $names, $priority, sizeof($names)]);

		if (sizeof($names) <= 0)
			return $this;
		foreach ($names as $name)
			$this->addTextField($name, $mode, $priority);
		return $this;
	}

	/**
	 * Searches for a text value in more then one column. Allows you to specify which columns and priorities to search for.
	 * Will search for the $params[$name] value in the $name field in the documents.
	 * Adds a 'multi_match' search.
	 *
	 * @param string $name The name of the field in the search parameters.
	 * @param array $fields The list of fields to search for; allows appending "^n" to designate weights (eg. ['name^3', 'nickname'])
	 * @param string $priority The 'priority' to use (must/should/filter). See ElasticSearch docs for more info.
	 * @return self Same query instance, for fluid composition.
	 */
	public function searchTextInColumns(string $name, array $fields = [], string $priority = 'must'): self
	{
		array_push($this->attemptedQuery, ['searchTextInColumns', $name, $fields, $priority, $this->params[$name] ?? null, sizeof($fields)]);

		if (!isset($this->params[$name]))
			return $this;
		if (strlen($this->params[$name]) <= 0)
			return $this;
		if (sizeof($fields) <= 0)
			return $this;

		array_push($this->query['bool'][$priority], [
			'multi_match' => [
				'query' => $this->params[$name],
				'fields' => $fields,
			]
		]);

		return $this;
	}
	/**
	 * Searches for a text value in more then one column. Allows you to specify which columns and priorities to search for.
	 * Will search for the $params[$name] value in the $name field in the documents.
	 * Adds a 'multi_match' search.
	 *
	 * @param string $name The name of the field in the search parameters.
	 * @param array $fields The list of fields to search for; allows appending "^n" to designate weights (eg. ['name^3', 'nickname'])
	 * @param string $priority The 'priority' to use (must/should/filter). See ElasticSearch docs for more info.
	 * @return self Same query instance, for fluid composition.
	 */
	public function autocompleteTextInColumns(string $name, array $fields = [], string $priority = 'must'): self
	{
		array_push($this->attemptedQuery, ['autocompleteTextInColumns', $name, $fields, $priority, $this->params[$name] ?? null]);

		if (!isset($this->params[$name]))
			return $this;
		if (strlen($this->params[$name]) <= 0)
			return $this;
		if (sizeof($fields) <= 0)
			return $this;

		array_push($this->query['bool'][$priority], [
			'suggest' => [

				"prefix" => $this->params[$name],
				"completion" => [
					"field" => "name_ascii"
				],

			]
		]);

		return $this;
	}

	/**
	 * Filters the documents that contains the term given. Allows you to include documents missing the given field.
	 * Will search for the $params[$name] value in the $name field in the documents.
	 * Adds a 'terms' search.
	 * When including null fields, will push for a 'bool' section with two 'should' queries: 'range' and 'missing'.
	 *
	 * @param string $name The name of the field in both search parameters and document.
	 * @param bool $includeNullFields Should the results include the documents that are missing the field?
	 * @param string $priority The 'priority' to use (must/should/filter). See ElasticSearch docs for more info.
	 * @param string $requirement Required or optional filtering
	 * @return self Same query instance, for fluid composition.
	 */
	public function filterByTerm(string $name, bool $includeNullFields = false, string $priority = 'filter', string $requirement = "should"): self
	{
		array_push($this->attemptedQuery, ['filterByTerm', $name, $includeNullFields, $priority, $this->params[$name] ?? null]);

		if (!isset($this->params[$name]))
			return $this;
		if (strlen($this->params[$name]) <= 0)
			return $this;

		$filter = ['bool' => [$requirement => [['term' => [$name => $this->params[$name]]]]]];

		if ($includeNullFields)
			array_push($filter['bool'][$requirement], ['missing' => ['field' => $name]]);

		array_push($this->query['bool'][$priority], $filter);

		return $this;
	}

	/**
	 * Filters the documents that contain atleast one of the terms given. Allows you to include documents missing the given field.
	 * Will search for the $params[$name] value in the $name field in the documents.
	 * Adds a 'terms' search.
	 * When including null fields, will push for a 'bool' section with two 'should' queries: 'range' and 'missing'.
	 *
	 * @param string $name The name of the field in both search parameters and document.
	 * @param bool $includeNullFields Should the results include the documents that are missing the field?
	 * @param string $priority The 'priority' to use (must/should/filter). See ElasticSearch docs for more info.
	 * @return self Same query instance, for fluid composition.
	 */
	public function filterByTerms(string $name, bool $includeNullFields = false, string $priority = 'filter'): self
	{
		array_push($this->attemptedQuery, ['filterByTerms', $name, $includeNullFields, $priority, $this->params[$name] ?? null]);

		if (!isset($this->params[$name]))
			return $this;
		if (!is_array($this->params[$name]))
			return $this;

		$filter = ['bool' => ['should' => [['terms' => [$name => $this->params[$name]]]]]];

		if ($includeNullFields)
			array_push($filter['bool']['should'], ['missing' => ['field' => $name]]);

		array_push($this->query['bool'][$priority], $filter);

		return $this;
	}

	public function filterByOneOf(array $conditions, $includeNullFields = false, $priority = 'filter'): self
	{
		array_push($this->attemptedQuery, ['filterByOneOf', $conditions]);

		$filter = ['bool' => ['should' => []]];

		foreach ($conditions as $field => $params) {
			array_push($filter['bool']['should'], [$params['type'] => [$field => $params['search']]]);
			if ($includeNullFields)
				array_push($filter['bool']['should'], ['missing' => ['field' => $field]]);
		}

		array_push($this->query['bool'][$priority], $filter);

		return $this;
	}

	/**
	 * Filters the document by a numeric range in the given field. Allows you to include documents missing the given field.
	 * Will search for the $params[$name] value in the $name field in the documents.
	 * Adds a 'range' search.
	 * When including null fields, will push for a 'bool' section with two 'should' queries: 'range' and 'missing'.
	 *
	 * @param string $name The name of the field in both search parameters and document.
	 * @param bool $includeNullFields Should the results include the documents that are missing the field?
	 * @param string $priority The 'priority' to use (must/should/filter). See ElasticSearch docs for more info.
	 * @return self Same query instance, for fluid composition.
	 */
	public function filterByRange(string $name, bool $includeNullFields = false, string $priority = 'filter'): self
	{
		array_push($this->attemptedQuery, ['filterByRange', $name, $priority, $this->params[$name] ?? null, is_array($this->params[$name] ?? null)]);

		if (!isset($this->params[$name]))
			return $this;
		if (!is_array($this->params[$name]))
			return $this;

		$filter = ['bool' => ['should' => [['range' => [$name => $this->params[$name]]]]]];

		if ($includeNullFields)
			array_push($filter['bool']['should'], ['missing' => ['field' => $name]]);

		array_push($this->query['bool'][$priority], $filter);

		return $this;
	}

	/**
	 * Gets the resulting ElasticSearch query object.
	 *
	 * @return array
	 */
	public function getQuery(): array
	{
		return $this->query;
	}

	public function getAttemptedQuery(): array
	{
		return $this->attemptedQuery;
	}

	public function appendBoolQuery($priority, $query)
	{
		array_push($this->attemptedQuery, ['appendedQuery', $priority, $query]);
		array_push($this->query['bool'][$priority], $query);
	}

	/**
	 * Creates a search query and gives it a specified set of search parameters.
	 *
	 * @param array $params The search parameters (associative array of fields to search and expected values).
	 * @return self The created search query, for fluid composition.
	 */
	public static function withParameters(array $params): self
	{
		return new self($params);
	}


	public function getNonInformedCases(int $validate, array $params)
	{
		if ($validate == 1) {
			$this->query['bool']['must_not']['exists']['field'] = 'case_cause_ids';
		} else {
			$i = count($this->query['bool']['must']);
			foreach ($params['case_cause_ids'] as $param)
				$this->query['bool']['must'][$i++]['match']['case_cause_ids'] = $param;
		}
		return $this->query;
	}
	
	
	public function getGroups(array $params)
	{
		$i = count($this->query['bool']['must']);
		if (array_key_exists('tree', $params) && $params['tree'] == 1)
			return $this->query['bool']['must'][$i++]['match_phrase']['tree_id'] = $params['group_id'];
		if (array_key_exists('group_id', $params) || array_key_exists('tree', $params) && $params['tree'] == 0)
			return $this->query['bool']['must'][$i++]['match']['group_id'] = $params['group_id'];
		return $this->query['bool']['must'][$i++]['match_phrase']['tree_id'] = \Auth::user()->group_id;
	}

}