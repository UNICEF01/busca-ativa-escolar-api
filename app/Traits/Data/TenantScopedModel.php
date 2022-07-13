<?php
/**
 * busca-ativa-escolar-api
 * TenantScoped.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/12/2016, 21:22
 */

namespace BuscaAtivaEscolar\Traits\Data;


use BuscaAtivaEscolar\Scopes\TenantScope;

trait TenantScopedModel {

	public static function bootTenantScopedModel() {
		static::addGlobalScope(new TenantScope());
	}

	public static function allTenants() {
		return with(new static())->newQueryWithoutScope(new TenantScope());
	}

	public static function withoutGlobalScope() {
		return with(new static())->newQueryWithoutScope(new TenantScope());
	}

	public function getTenantWhereClause($tenantColumn, $tenantId) {
		return "{$this->getTable()}.{$tenantColumn} = '{$tenantId}''";
	}

}