<?php

/**
 * busca-ativa-escolar-api
 * ReportsController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 01/02/2017, 17:22
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;


use Auth;
use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\DailyTests;
use BuscaAtivaEscolar\Data\AgeRange;
use BuscaAtivaEscolar\Data\AlertCause;
use BuscaAtivaEscolar\Data\CaseCause;
use BuscaAtivaEscolar\Data\IncomeRange;
use BuscaAtivaEscolar\Data\Race;
use BuscaAtivaEscolar\Data\SchoolingLevel;
use BuscaAtivaEscolar\Data\WorkActivity;
use BuscaAtivaEscolar\HistoricalTenant;
use BuscaAtivaEscolar\HistoricalTenantSignup;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Exports\RepostsExport;
use BuscaAtivaEscolar\IBGE\Region;
use BuscaAtivaEscolar\IBGE\UF;
use BuscaAtivaEscolar\Jobs\ProcessReportSeloJob;
use BuscaAtivaEscolar\Reports\Reports;
use BuscaAtivaEscolar\School;
use BuscaAtivaEscolar\Search\ElasticSearchQuery;
use BuscaAtivaEscolar\StateSignup;
use BuscaAtivaEscolar\TenantSignup;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\User;
use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelB;

class ReportsController extends BaseController
{
    private $excel;

    public function __construct(ExcelB $excel)
    {
        $this->excel = $excel;
    }

    public function query_children(Reports $reports)
    {

        // TODO: this needs a major refactoring to clear up the complexity that crept in

        $params = request()->all();
        $filters = request('filters', []);
        $format = request('format', 'json');


        // Verifica se usuário está restrito a município
        if (Auth::user()->isRestrictedToTenant()) $filters['tenant_id'] = Auth::user()->tenant_id;

        // Verifica se usuário está restrito a estado
        if (Auth::user()->isRestrictedToUF()) $filters['uf'] = Auth::user()->uf;

        //Verifica se a cidade foi informada no filtro. Neste caso remove o filtro de cidade e cria-se um filtro de tenant
        if (isset($filters['place_city'])) {

            $tenant = Tenant::where('city_id', $filters['place_city_id'])->withTrashed()->first();

            if ($tenant != null) {
                $filters['tenant_id'] = $tenant->id;
                $tenant = true;
            } else {
                $tenant = false;
            }

            unset($filters['place_city']);
            unset($filters['place_city_id']);
            unset($filters['place_uf']);
        }

        if (Auth::user()->isRestrictedToTenant()) $filters['tenant_id'] = Auth::user()->tenant_id;

        if (isset($filters['place_uf'])) $filters['place_uf'] = Str::lower($filters['place_uf']);
        if (isset($filters['uf'])) $filters['uf'] = Str::lower($filters['uf']);

        if (!isset($params['view'])) {
            $params['view'] = "linear";
        }

        $entity = new Child();
        $query = ElasticSearchQuery::withParameters($filters)
            ->filterByTerm('tenant_id', false, 'filter', Auth::user()->isRestrictedToTenant() ? 'must' : 'should')
            ->filterByTerm('uf', false, 'filter', Auth::user()->isRestrictedToUF() ? 'must' : 'should')
            ->filterByTerms('case_status', false)
            ->filterByTerms('alert_status', false)
            ->filterByTerm('step_slug', false)
            ->filterByTerm('place_uf', $filters['place_uf_null'] ?? false)
            ->filterByTerm('place_city_id', $filters['place_city_id_null'] ?? false)
            ->filterByTerm('case_cause_ids', false)
            ->filterByTerms('child_status', false)
            ->filterByTerm('school_last_grade', $params['school_last_grade_null'] ?? false)
            ->filterByTerms('risk_level', $filters['risk_level_null'] ?? false)
            ->filterByTerms('gender', $filters['gender_null'] ?? false)
            ->filterByTerms('place_kind', $filters['place_kind_null'] ?? false);

        //->filterByTerms('deadline_status', false)
        //->filterByTerm('race',$filters['race_null'] ?? false)
        //->filterByTerm('guardian_schooling',$filters['guardian_schooling_null'] ?? false)
        //->filterByTerm('parents_income',$filters['parents_income_null'] ?? false)

        if ($params['view'] == "time_series") {
            if (!isset($filters['date'])) $filters['date'] = ['lte' => 'now', 'gte' => 'now-2d'];
            $query->filterByRange('date', false);
        }

        if ($params['view'] == "linear") {
            if (!isset($filters['created_at'])) $filters['created_at'] = ['lte' => 'now', 'gte' => 'now-2d'];
            $query->filterByRange('created_at', false);
        }

        //for age ranges:
        $ageRanges = isset($filters['age_ranges']) ? $filters['age_ranges'] : null;
        $nullAges = $filters['age_null'] ?? false;
        //-------

        if ($ageRanges != null and $params['dimension'] != 'age') {

            $rangesQuery = collect($filters['age_ranges'])->map(function ($rangeSlug) {
                $range = AgeRange::getBySlug($rangeSlug);
                return ['range' => ['age' => ['from' => $range->from, 'to' => $range->to]]];
            });

            $ageQuery = ['should' => [$rangesQuery->toArray()]];

            if ($filters['age_null'] ?? false) {
                array_push($ageQuery['should'], ['missing' => ['field' => 'age']]);
            }

            $query->appendBoolQuery('filter', ['bool' => $ageQuery]);
        }

        $index = ($params['view'] == 'linear') ? $entity->getAggregationIndex() : $entity->getTimeSeriesIndex();
        $type = ($params['view'] == 'linear') ? $entity->getAggregationType() : $entity->getTimeSeriesType();


        try {
            $response = ($params['view'] == 'time_series') ?
                $reports->timeline($index, $type, $params['dimension'], $query) :
                $reports->linear($index, $type, $params['dimension'], $query, $ageRanges, $nullAges);

            $ids = $this->extractDimensionIDs($response['report'], $params['view']);
            $labels = $this->fetchDimensionLabels($params['dimension'], $ids);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }

        if ($format === 'xls') {
            return $this->exportResults($params['view'], $response, $labels);
        }

        if (isset($tenant)) {
            $response['tenant'] = $tenant;
        }

        //remove IDS 500 e 600 dos motivos dos casos
        if ($params['dimension'] == "case_cause_ids") {
            if (array_key_exists(500, $response['report'])) {
                unset($response['report'][500]);
            }
            if (array_key_exists(600, $response['report'])) {
                unset($response['report'][600]);
            }
        }

        return response()->json(
            [
                'query' => $query->getQuery(),
                'attempted' => $query->getAttemptedQuery(),
                'response' => $response,
                'labels' => $labels,
            ]
        );
    }

    public function query_tenants()
    {

        $filters = request('filters', []);
        $format = request('format', 'json');

        if (isset($filters['uf'])) $filters['uf'] = Str::lower($filters['uf']);

        // Scope the query within the tenant
        if (Auth::user()->isRestrictedToTenant()) $filters['tenant_id'] = Auth::user()->tenant_id;
        if (Auth::user()->isRestrictedToUF()) $filters['uf'] = Auth::user()->uf;

        $query = Tenant::query();

        if (isset($filters['uf'])) $query->where('uf', $filters['uf']);

        $tenants = $query->get();
        $recordsTotal = $tenants->count();

        $report = null;
        $labels = [];

        switch (request('dimension')) {

            case "uf":

                $report = $tenants
                    ->sortBy('uf')
                    ->groupBy('uf')
                    ->map(function ($group) {
                        return $group->count();
                    });

                $labels = $report->keys()->sort();

                break;

            case "region":

                $labels = collect(Region::getAll())->pluck('name', 'id');

                $report = collect(Region::getAll())
                    ->sortBy('name')
                    ->map(function ($region) use ($tenants, $labels) {
                        $ufs = collect(UF::getAll())
                            ->where('region_id', $region->id)
                            ->pluck('code')
                            ->toArray();

                        return [
                            'name' => $labels[$region->id],
                            'count' => $tenants->whereIn('uf', $ufs)->count()
                        ];
                    })
                    ->pluck('count', 'name');

                break;
        }

        $response = [
            'records_total' => $recordsTotal,
            'report' => $report,
        ];

        if ($format === 'xls') {
            return $this->exportResults('linear', $response, $labels);
        }

        return response()->json([
            'response' => $response,
            'labels' => $labels
        ]);
    }

    public function query_ufs()
    {

        $ufs = collect(UF::getAllByCode());
        $regionLabels = collect(Region::getAll())->sortBy('name')->pluck('name', 'id');

        $dimension = request('dimension');
        $format = request('format', 'json');

        $report = DB::table("users")
            ->whereIn('type', User::$UF_SCOPED_TYPES)
            ->groupBy('uf')
            ->select(['uf', DB::raw('COUNT(id) as num')])
            ->get()
            ->map(function ($user) use ($ufs, $regionLabels) {
                $user->region_id = $ufs[$user->uf]['region_id'];
                $user->region_name = $labels[$user->region_id] ?? '';
                return $user;
            });

        switch ($dimension) {
            case "uf":
                $seriesName = 'Número de usuários por estado';
                $report = $report
                    ->sortBy('uf')
                    ->keyBy('uf')
                    ->map(function ($dimension) {
                        return $dimension->num;
                    });

                break;

            case "region":
            default:
                $seriesName = 'Número de estados participantes';
                $report = $report
                    ->groupBy('region_id')
                    ->sortBy('region_name')
                    ->map(function ($dimension) {
                        return $dimension->count();
                    });

                break;
        }

        $recordsTotal = $report->sum();
        $response = [
            'records_total' => $recordsTotal,
            'report' => $report,
            'seriesName' => $seriesName,
        ];

        if ($format === 'xls') {
            return $this->exportResults('linear', $response, $regionLabels);
        }

        return response()->json([
            'response' => $response,
            'labels' => $regionLabels
        ]);
    }

    public function query_signups()
    {

        $today = Carbon::now();
        $format = request('format', 'json');

        $numSignups = DB::table("tenant_signups")
            ->select([DB::raw('CONCAT(YEAR(created_at), CONCAT("-", MONTH(created_at))) as month'), DB::raw('COUNT(id) as qty')])
            ->groupBy('month')
            ->get()
            ->pluck('qty', 'month');

        $lastTwelveMonths = collect(range(0, 11))
            ->reverse()
            ->map(function ($i) use ($today) {
                $date = $today->copy()->addMonths(-$i);

                return [
                    'index' => $i,
                    'date' => $date->format('Y-m') . "-01",
                    'month' => $date->format('Y-n'),
                    'label' => $date->formatLocalized('%b/%Y'),
                ];
            })
            ->keyBy('label')
            ->map(function ($period) use ($numSignups) {
                //$this->debug_push('period', $period['month'], $numSignups[$period['month']] ?? null);
                return ['num_tenant_signups' => $numSignups[$period['month']] ?? 0];
            });

        //return $this->debug_output();


        $labels = ['num_tenant_signups' => 'Qtd. de adesões municipais'];
        $response = [
            'records_total' => 0,
            'report' => $lastTwelveMonths,
        ];

        if ($format === 'xls') {
            return $this->exportResults('linear', $response, $labels);
        }

        return response()->json([
            'response' => $response,
            'labels' => $labels
        ]);
    }

    private function exportResults($view, $response, $labels)
    {

        $exportFile = uniqid("report_export_", true);
        $exportFolder = storage_path('app/export/' . auth()->user()->id);

        if ($view === 'linear') {

            $data = collect($response['report'])
                ->map(function ($value, $column) use ($labels) {
                    return [$labels[$column] ?? $column, $value ?? 0];
                })
                ->values()
                ->toArray();
        } else if ($view === "time_series") { // TODO: optimize for performance

            $header = null;
            $data = collect($response['report'])
                ->map(function ($stats, $date) use ($labels, &$header) {
                    if ($header === null) {
                        $header = collect($stats)
                            ->map(function ($_, $column) use ($labels) {
                                return $labels[$column] ?? $column;
                            })
                            ->values()
                            ->toArray();
                    }

                    if ($stats != null) {
                        array_unshift($stats, $date);
                    }

                    return collect($stats)->values()->toArray();
                })
                ->values()
                ->toArray();

            if ($header === null) { // In case no header was built due to no records found
                $header = [];
            }

            array_unshift($header, 'Data');
            array_unshift($data, $header);
        }
        $this->excel->store(new RepostsExport($data), 'attachment/buscaativaescolar_user/' . auth()->user()->id . '/' . auth()->user()->id . '.xls');
        $token = \JWTAuth::fromUser(auth()->user());
        return $this->api_success([
            'export_file' => auth()->user()->id . '.xls',
            'download_url' => route('api.reports.download_exported', ['filename' => auth()->user()->id . '.xls', 'token' => $token])
        ]);
    }

    public function download_exported($filename)
    {

        $token = request('token');

        \JWTAuth::invalidate($token);

        return response()->download(storage_path('app/attachment/buscaativaescolar_user/' . auth()->user()->id . '/' . basename($filename)));
    }

    public function country_stats()
    {

        try {

            $stats = Cache::remember('stats_country', config('cache.timeouts.stats_platform'), function () {

                return [

                    //Inicio do ciclo 2

                    'num_tenants' => Tenant::query()
                        ->count(),

                    'num_ufs' => StateSignup::query()->count(),

                    'num_signups' => TenantSignup::query()
                        ->count(),

                    'num_pending_setup' => TenantSignup::query()
                        ->where('is_approved', '=', 1)
                        ->where('is_provisioned', '=', 0)
                        ->count(),

                    'num_alerts' => Alerta::query()
                        ->accepted()
                        ->count(),

                    'num_pending_alerts' => Child::whereHas('alert', function ($query) {
                        $query->where('alert_status', '=', 'pending');
                    })->pending()->count(),

                    'num_rejected_alerts' => Child::whereHas('alert', function ($query) {
                        $query->where('alert_status', '=', 'rejected');
                    })->rejected()->count(),

                    'num_total_alerts' => ChildCase::query()
                        ->count(),

                    'num_cases_in_progress' => Child::with(['currentCase'])
                        ->hasCaseInProgress()
                        ->count(),

                    'num_children_reinserted' => Child::query()
                        ->whereIn('child_status', [Child::STATUS_IN_SCHOOL, Child::STATUS_OBSERVATION])
                        ->count(),

                    'num_pending_signups' => TenantSignup::query()
                        ->whereNull('judged_by')
                        ->count(),

                    'num_pending_state_signups' => StateSignup::query()
                        ->whereNull('judged_by')
                        ->count(),

                    'num_children_in_school' => Child::query()
                        ->where('child_status', '=', Child::STATUS_IN_SCHOOL)
                        ->count(),

                    'num_children_in_observation' => Child::query()
                        ->where('child_status', '=', Child::STATUS_OBSERVATION)
                        ->count(),

                    'num_children_out_of_school' => Child::query()
                        ->where('child_status', '=', Child::STATUS_OUT_OF_SCHOOL)
                        ->where('alert_status', '=', Child::ALERT_STATUS_ACCEPTED)
                        ->count(),

                    'num_children_cancelled' => Child::query()
                        ->where('child_status', '=', Child::STATUS_CANCELLED)
                        ->where('alert_status', '=', Child::ALERT_STATUS_ACCEPTED)
                        ->count(),

                    'num_children_transferred' => Child::query()
                        ->where('child_status', '=', Child::STATUS_TRANSFERRED)
                        ->count(),

                    'num_children_interrupted' => Child::query()
                        ->where('child_status', '=', Child::STATUS_INTERRUPTED)
                        ->count(),

                    // final do ciclo 2

                ];
            });

            return response()->json(['status' => 'ok', 'stats' => $stats]);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    public function state_stats()
    {

        $uf = request('uf', $this->currentUser()->uf);

        if (!$uf) {
            return $this->api_failure('invalid_uf');
        }

        $tenantIDs = Tenant::getIDsWithinUF($uf);
        $cityIDs = City::getIDsWithinUF($uf);

        try {

            $stats = Cache::remember('stats_state_' . $uf, config('cache.timeouts.stats_platform'), function () use ($uf, $cityIDs, $tenantIDs) {
                return [
                    'num_tenants' => Tenant::query()
                        ->where('uf', $uf)
                        ->count(),

                    'num_signups' => TenantSignup::query()
                        ->whereIn('city_id', $cityIDs)
                        ->count(),

                    'num_pending_setup' => TenantSignup::query()
                        ->whereIn('city_id', $cityIDs)
                        ->where('is_approved', 1)
                        ->where('is_provisioned', 0)
                        ->count(),

                    'num_alerts' => Alerta::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->notRejected()
                        ->count(),

                    'num_cases_in_progress' => ChildCase::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->where('case_status', ChildCase::STATUS_IN_PROGRESS)
                        ->count(),

                    'num_children_reinserted' => Child::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->whereIn('child_status', [Child::STATUS_IN_SCHOOL, Child::STATUS_OBSERVATION])
                        ->count(),

                    'num_pending_signups' => TenantSignup::query()
                        ->whereIn('city_id', $cityIDs)
                        ->whereNull('judged_by')
                        ->count(),

                    //new options

                    'num_total_alerts' => Alerta::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->count(),

                    'num_accepted_alerts' => Alerta::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->where('alert_status', '=', Child::ALERT_STATUS_ACCEPTED)
                        ->count(),

                    'num_pending_alerts' => Alerta::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->where('alert_status', '=', Child::ALERT_STATUS_PENDING)
                        ->count(),

                    'num_rejected_alerts' => Alerta::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->where('alert_status', '=', Child::ALERT_STATUS_REJECTED)
                        ->count(),

                    'num_children_in_school' => Child::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->where('child_status', '=', Child::STATUS_IN_SCHOOL)
                        ->count(),

                    'num_children_out_of_school' => Child::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->where([
                            ['child_status', '=', Child::STATUS_OUT_OF_SCHOOL],
                            ['alert_status', '=', Child::ALERT_STATUS_ACCEPTED]
                        ])
                        ->count(),

                    'num_children_in_observation' => Child::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->where('child_status', '=', Child::STATUS_OBSERVATION)
                        ->count(),

                    'num_children_cancelled' => Child::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->where([
                            ['child_status', '=', Child::STATUS_CANCELLED],
                            ['alert_status', '=', Child::ALERT_STATUS_ACCEPTED]
                        ])
                        ->count(),

                    'num_children_transferred' => Child::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->where([
                            ['child_status', '=', Child::STATUS_TRANSFERRED]
                        ])
                        ->count(),

                    'num_children_interrupted' => Child::query()
                        ->whereIn('tenant_id', $tenantIDs)
                        ->where([
                            ['child_status', '=', Child::STATUS_INTERRUPTED],
                        ])
                        ->count(),


                ];
            });

            return response()->json(['status' => 'ok', 'stats' => $stats, 'uf' => $uf, 'tenant_ids' => $tenantIDs, 'city_ids' => $cityIDs]);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    protected function extractDimensionIDs($report, $view)
    {
        if ($view !== 'time_series') return array_keys($report ?? []);

        $results = collect($report)->map(function ($results) {
            return array_keys($results ?? []);
        })->toArray();

        return array_reduce($results, function ($carry, $item) {
            return array_merge($carry ?? [], $item);
        });
    }

    protected function fetchDimensionLabels($dimension, $ids = [])
    {

        $hasIDs = is_array($ids) && sizeof($ids) > 0;

        switch ($dimension) {
            case 'case_status':
                return trans('reports_terms.status');
            case 'child_status':
                return trans('reports_terms.status');
            case 'step_slug':
                return trans('reports_terms.name_by_slug');
            case 'age':
                return trans('reports_terms.age_ranges');
            case 'gender':
                return trans('reports_terms.gender');
            case 'parents_income':
                return trans('reports_terms.parents_income');
            case 'place_kind':
                return trans('reports_terms.place_kind');
            case 'work_activity':
                return trans('reports_terms.work_activity');
            case 'case_cause_ids':
                return trans('reports_terms.case_causes');
            case 'alert_cause_id':
                return array_pluck(AlertCause::getAllAsArray(), 'label', 'id');
            case 'place_uf':
                return trans('reports_terms.place_uf');
            case 'uf':
                return trans('reports_terms.place_uf');
            case 'place_city_id':
                return $hasIDs ? City::whereIn('id', $ids)->get()->pluck('name', 'id') : []; // TODO: create full_name field that contains UF
            case 'city_id':
                return $hasIDs ? City::whereIn('id', $ids)->get()->pluck('name', 'id') : []; // TODO: create full_name field that contains UF
            case 'school_last_id':
                return $hasIDs ? School::whereIn('id', $ids)->get()->pluck('name', 'id') : [];
            case 'race':
                return trans('reports_terms.races');
            case 'guardian_schooling':
                return trans('reports_terms.guardian_schooling');
            case 'country_region':
                return trans('reports_terms.country_region');
            case 'school_last_grade':
                return trans('reports_terms.school_last_grade');
            case 'child_status_by_tenant':
                return trans('reports_terms.child_status_by_tenant');
            case 'alert_status_report_by_tenant':
                return trans('reports_terms.alert_status_report_by_tenant');
            default:
                return [];
        }
    }

    public function createSeloReport()
    {

        dispatch((new ProcessReportSeloJob())->onQueue('export_users'));
        return response()->json(
            [
                'msg' => 'Arquivo criado',
                'date' => Carbon::now()->timestamp
            ],
            200
        );
    }

    public function getSeloReports()
    {
        $reports = \Storage::allFiles('attachments/selo_reports');
        $finalReports = array_map(function ($file) {
            return [
                'file' => str_replace("attachments/selo_reports/", "", $file),
                'size' => \Storage::size($file),
                'last_modification' => \Storage::lastModified($file)
            ];
        }, $reports);
        return response()->json(['status' => 'ok', 'data' => $finalReports]);
    }

    public function getSeloReport()
    {
        $nameFile = request('file');
        if (!isset($nameFile)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }
        $exists = \Storage::exists("attachments/selo_reports/" . $nameFile);
        if ($exists) {
            return response()->download(storage_path("app/attachments/selo_reports/" . $nameFile));
        } else {
            return response()->json(['error' => 'Arquivo inexistente.'], 403);
        }
    }

    public function query_children_by_tenant(Reports $reports)
    {

        $params = request()->all();
        $params['view'] = "time_series";

        $filtersChild = request('filters_child_status', []);
        $filtersAlert = request('filters_alert_status', []);

        $year = request('year', 2017);

        $entity = new Child();

        Tenant::withTrashed()->chunk(100, function ($tenants) use ($year, $filtersChild, $filtersAlert, $entity, $reports, $params) {

            foreach ($tenants as $tenant) {

                $begin = new \DateTime(strval($year) . "-01-01");
                $end   = new \DateTime(strval($year + 1) . "-01-01");

                for ($i = $begin; $i <= $end; $i->modify('+1 day')) {

                    $dayOfMonth = $i->format("Y-m-d");
                    $dayOfMonthptBr = $i->format("d/m/Y");
                    $dayOfMonthCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $dayOfMonth . " 23:59:59");

                    if ($dayOfMonthCarbon->greaterThan($tenant->created_at)) {

                        $filtersChild['date'] = [
                            'from' => $dayOfMonth,
                            'to' => $dayOfMonth
                        ];

                        $filtersAlert['date'] = $filtersChild['date'];

                        $filtersChild['tenant_id'] = $tenant->id;

                        $filtersAlert['tenant_id'] = $filtersChild['tenant_id'];

                        $queryChild = $this->returnQueryForChildrenByTenant($filtersChild);

                        $queryAlert = $this->returnQueryForChildrenByTenant($filtersAlert);

                        $index = $entity->getTimeSeriesIndex();
                        $type = $entity->getTimeSeriesType();

                        try {
                            $responseChild = $reports->timeline($index, $type, "child_status", $queryChild);
                            $responseAlert = $reports->timeline($index, $type, "alert_status", $queryAlert);

                            $idsChildStatus = $this->extractDimensionIDs($responseChild['report'], $params['view']);
                            $labelsChildStatus = $this->fetchDimensionLabels("child_status_by_tenant", $idsChildStatus);

                            $idsAlertStatus = $this->extractDimensionIDs($responseAlert['report'], $params['view']);
                            $labelsAlertStatus = $this->fetchDimensionLabels("alert_status_report_by_tenant", $idsAlertStatus);
                        } catch (\Exception $ex) {
                            return $this->api_exception($ex);
                        }

                        $values = [];
                        foreach ($labelsAlertStatus as $key => $label) {
                            if (sizeof($responseAlert["report"]) > 0) {
                                $values[$key] = array_key_exists($key, $responseAlert["report"][$dayOfMonth]) ? $responseAlert["report"][$dayOfMonth][$key] : 0;
                            } else {
                                $values[$key] = 0;
                            }
                        }

                        foreach ($labelsChildStatus as $key => $label) {
                            if (sizeof($responseChild["report"]) > 0) {
                                $values[$key] = array_key_exists($key, $responseChild["report"][$dayOfMonth]) ? $responseChild["report"][$dayOfMonth][$key] : 0;
                            } else {
                                $values[$key] = 0;
                            }
                        }

                        $fp = fopen('/home/forge/reports_children_daily_by_year/' . strval($year) . '.csv', 'a');
                        fwrite($fp, "\n\"" . $tenant->created_at->format('d/m/Y') . "\",\"" . $dayOfMonth . "\",\"" . $tenant->uf . "\",\"" . str_replace($tenant->uf . " / ", "", $tenant->name) . "\"," . implode(",", $values));
                        fclose($fp);
                    }
                }
            }
        });
    }

    private function returnQueryForChildrenByTenant($filters)
    {
        return ElasticSearchQuery::withParameters($filters)
            ->filterByTerms('case_status', false)
            ->filterByTerms('alert_status', false)
            ->filterByTerm('step_slug', false)
            ->filterByTerm('place_uf', false)
            ->filterByTerm('place_city_id', false)
            ->filterByTerm('case_cause_ids', false)
            ->filterByTerms('child_status', false)
            ->filterByTerm('school_last_grade', false)
            ->filterByTerms('risk_level', false)
            ->filterByTerms('gender', false)
            ->filterByTerms('place_kind', false)
            ->filterByRange('date', false)
            ->filterByTerm('tenant_id', false, 'must');
    }

    public function query_children_tests()
    {

        $daily_data = DB::table("daily_metrics_consolidated")
            ->select(
                'date',
                DB::raw('SUM(out_of_school) as casos_andamento_fora_da_escola'),
                DB::raw('SUM(in_observation) as casos_andamentto_dentro_da_escola'),
                DB::raw('SUM(in_school) as casos_concluidos'),
                DB::raw('SUM(cancelled) as casos_cancelados'),
                DB::raw('SUM(interrupted) as casos_interrompidos'),
                DB::raw('SUM(transferred) as casos_transferidos')
            )
            ->groupBy('date')
            ->get();

        return response()->json(['status' => 'ok', 'data' => $daily_data]);

        /* $daily_data = DB::table("daily_metrics_consolidated")
            ->select('data_relatorio',
                DB::raw('SUM(out_of_school) as casos_andamento_fora_da_escola'),
                DB::raw('SUM(in_observation) as casos_andamentto_dentro_da_escola'),
                DB::raw('SUM(in_school) as casos_concluidos'),
                DB::raw('SUM(cancelled) as casos_cancelados'),
                DB::raw('SUM(interrupted) as casos_interrompidos'),
                DB::raw('SUM(transferred) as casos_transferidos')
            )
            ->groupBy('date')
            ->get();

        return response()->json(['status' => 'ok', 'data' => $daily_data]); */
    }
}
