<?php

/**
 * busca-ativa-escolar-api
 * StaticDataController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 10/01/2017, 18:31
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;

use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\User;
use Route;

class StaticDataController extends BaseController
{

	public function render()
	{

		// TODO: cache this

		return response()->json([
			'version' => 'latest',
			'timestamp' => date('Y-m-d\TH:i:s'),
			'data' => [
				'AlertCause' => \BuscaAtivaEscolar\Data\AlertCause::getAllAsArray(),
				'VisibleAlertCause' => \BuscaAtivaEscolar\Data\AlertCause::getAllVisible(),
				'CaseCause' => \BuscaAtivaEscolar\Data\CaseCause::getAllAsArray(),
				'VisibleCaseCause' => \BuscaAtivaEscolar\Data\CaseCause::getAllVisible(),
				'Gender' => \BuscaAtivaEscolar\Data\Gender::getAllAsArray(),
				'HandicappedRejectReason' => \BuscaAtivaEscolar\Data\HandicappedRejectReason::getAllAsArray(),
				'IncomeRange' => \BuscaAtivaEscolar\Data\IncomeRange::getAllAsArray(),
				'AgeRange' => \BuscaAtivaEscolar\Data\AgeRange::getAllAsArray(),
				'PlaceKind' => \BuscaAtivaEscolar\Data\PlaceKind::getAllAsArray(),
				'GuardianType' => \BuscaAtivaEscolar\Data\GuardianType::getAllAsArray(),
				'SchoolLastStatus' => \BuscaAtivaEscolar\Data\SchoolLastStatus::getAllAsArray(),
				'Race' => \BuscaAtivaEscolar\Data\Race::getAllAsArray(),
				'SchoolGrade' => \BuscaAtivaEscolar\Data\SchoolGrade::getAllAsArray(),
				'SchoolingLevel' => \BuscaAtivaEscolar\Data\SchoolingLevel::getAllAsArray(),
				'WorkActivity' => \BuscaAtivaEscolar\Data\WorkActivity::getAllAsArray(),
				'UserType' => \BuscaAtivaEscolar\User::$ALLOWED_TYPES,
				'UserTypeVisitantes' => \BuscaAtivaEscolar\User::$ALLOWED_TYPES_VISITANTES,
				'PermissionsFormForVisitante' => \BuscaAtivaEscolar\User::$PERMISSIONS_FORM_FOR_VISITANTE,
				'CaseStepSlugs' => \BuscaAtivaEscolar\CaseSteps\CaseStep::SLUGS,
				'UFs' => \BuscaAtivaEscolar\IBGE\UF::getAllAsArray(),
				'UFsByCode' => \BuscaAtivaEscolar\IBGE\UF::getAllByCode(),
				'Regions' => \BuscaAtivaEscolar\IBGE\Region::getAllAsArray(),
				'APIEndpoints' => $this->buildAPIEndpointList(),
				'CaseCancelReasons' => \BuscaAtivaEscolar\ChildCase::CANCEL_REASONS,
				'Permissions' => config('user_type_permissions', []),
				'UsersWithGlobalScope' => User::$GLOBAL_SCOPED_TYPES,
				'UsersWithUFScope' => User::$UF_SCOPED_TYPES,
				'Config' => [
					'uploads' => [
						'allowed_mime_types' => config('uploads.allowed_mime_types'),
					]
				],
				'Nationality' => \BuscaAtivaEscolar\Data\Nationality::getAllAsArray(),
			]
		]);
	}

	public function buildAPIEndpointList()
	{
		$methods = Route::getRoutes()->getRoutesByMethod();
		$list = [];

		$prefix = 'api/v1/';

		foreach ($methods as $method => $routes) {
			if ($method == "HEAD") continue;
			if ($method == "PATCH") continue;
			if ($method == "OPTIONS") continue;

			foreach ($routes as $route) {
				$path = $route->getName();
				if (substr($path, 0, 7) !== $prefix) continue;

				$path = substr($path, 7);

				array_push($list, [
					'name' => "{$method} {$path}",
					'method' => $method,
					'path' => $path,
					'action' => $route->getAction()
				]);
			}
		}

		return $list;
	}
}
