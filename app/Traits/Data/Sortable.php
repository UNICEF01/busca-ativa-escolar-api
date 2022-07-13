<?php
/**
 * busca-ativa-escolar-api
 * Sortable.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 13/03/2017, 17:30
 */

namespace BuscaAtivaEscolar\Traits\Data;


use DB;
use Illuminate\Database\Eloquent\Builder;

trait Sortable {

	public static function applySorting(Builder $query, array $sortArray) {
		$instance = new self();

		foreach($sortArray as $field => $mode) {

			$dotPosition = strpos($field, '.');

			// Dot-notation means sort by relationship
			if($dotPosition !== false) {
				$relation = substr($field, 0, $dotPosition);
				$fields = substr($field, $dotPosition + 1);

				$sortField = substr($fields, 0, strpos($fields, ':'));
				$keyField = substr($fields, strpos($fields, ':') + 1) ?? 'id';

				if(!in_array("{$relation}.{$sortField}", $instance->fillable)) continue;

				$query->join($relation, "{$relation}.id", '=', "{$instance->table}.{$keyField}");
				$query->orderBy("{$relation}.{$sortField}", $mode);

				continue;
			}

			$isFillable = in_array($field, $instance->fillable);
			$isSortable = isset($instance->sortable) && in_array($field, $instance->sortable);

			if(!$isFillable && !$isSortable) {
				continue;
			}

			// TODO: fix dot-notation sorting (need a way to resolve table/pk from relationship name to apply join())

			$query->orderBy($field, ($mode == 'desc') ? 'desc' : 'asc');
		}

		return $query;
	}

}