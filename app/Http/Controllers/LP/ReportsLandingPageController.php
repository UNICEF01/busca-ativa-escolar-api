<?php

/**
 * Created by PhpStorm.
 * User: manoelfilho
 * Date: 15/11/18
 * Time: 13:43
 */

namespace BuscaAtivaEscolar\Http\Controllers\LP;

use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Data\CaseCause;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\TenantSignup;
use Carbon\Carbon;
use Cache;
use BuscaAtivaEscolar\Cache\CacheService;
use DB;
use Matrix\Exception;

class ReportsLandingPageController extends BaseController
{

    public function report()
    {
        $resqueted = [
            'country' => request(''),
            'reg' => request('reg'),
            'state' => request('uf')
        ];
        try {
            $typeOfCache = 'country';
            foreach ($resqueted as $key => $value) {
                if (!empty($value))
                    $typeOfCache = $key;
            }
            $cache = new CacheService();
            return response()->json(['status' => 'ok', '_data' => $cache->returnData($resqueted[$typeOfCache] ? $resqueted[$typeOfCache] : 'BR')]);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    public function report_city()
    {
        $city = request('city');
        $uf = request('uf');
        $ibge_id = request('ibge_id');

        if ($city != null) {
            $tenant = Tenant::where([['name', '=', $uf . ' / ' . $city], ['is_active', '=', 1]])->withTrashed()->first();
        }

        if ($ibge_id != null) {
            $city_ibge = City::where('ibge_city_id', '=', intval($ibge_id))->first();
            if ($city_ibge) {
                if ($ibge_id == '3550308')
                    $tenant = Tenant::where([['city_id', '=', $city_ibge->id], ['is_active', '=', 1]])->first();
                else
                    $tenant = Tenant::where([['city_id', '=', $city_ibge->id], ['is_active', '=', 1]])->withTrashed()->first();
            } else
                $tenant = null;
        }

        $tenantId = $tenant ? $tenant->id : 0;

        if ($tenant != null) {

            $created = $tenant->created_at->format('d/m/Y');
            $now = Carbon::now();
            $last_active_at = $tenant->last_active_at;
            $lastTenantSignup = TenantSignup::where('tenant_id', $tenantId)->latest()->first();

            if ($now->diffInDays($last_active_at) >= 30) {
                $status = "Inativo";
            } else {
                $status = "Ativo";
            }

            $data_city = $data_city = ['created' => $created, 'status' => $status, 'last_tenant_signup' => $lastTenantSignup ? $lastTenantSignup->created_at->format('d/m/Y') : null];
        } else {
            $data_city = null;
            $data = [
                'alerts' => [],
                'cases' => [],
                'causes' => [],
                'data_city' => $data_city
            ];
            return response()->json(['status' => 'ok', 'stats' => $data]);
        }

        try {

            $stats = Cache::remember('report_city_' . $tenantId, 86400, function () use ($tenantId, $data_city) {

                $causes = [];

                foreach (CaseCause::getAll() as $case) {

                    if (!$case->hidden) {
                        $qtd =
                            \DB::table('children')
                            ->join('case_steps_pesquisa', 'children.id', '=', 'case_steps_pesquisa.child_id')
                            ->where(function ($query) use ($case, $tenantId) {
                                $query->whereJsonContains('case_steps_pesquisa.case_cause_ids', $case->id);
                                $query->where([
                                    ['case_steps_pesquisa.tenant_id', $tenantId],
                                    ['children.alert_status', 'accepted']
                                ]);
                            })
                            ->count();

                        if ($qtd > 0) {
                            array_push($causes, ['id' => $case->id, 'cause' => $case->label, 'qtd' => $qtd]);
                        }
                    }
                }

                $alerts = DB::select(
                    DB::raw("select t2.accepted, t1.pending, t2.rejected from(select `children`.`tenant_id`, count(1) as pending from `children` inner join `tenants` on `children`.`tenant_id` = `tenants`.`id` where exists(select count(1) from `case_steps_alerta` where `children`.`id` = `case_steps_alerta`.`child_id` and `alert_status` = 'pending' and `case_steps_alerta`.`deleted_at` is null) and `alert_status` = 'pending' and `children`.`deleted_at` is null and `children`.`tenant_id` = '{$tenantId}') as t1, (select `children`.`tenant_id`, sum(case when `case_steps_alerta`.`alert_status` = 'accepted' and `children`.`alert_status` = 'accepted' then 1 else 0 end) as accepted, sum(case when `children`.`alert_status` = 'rejected' then 1 else 0 end) as rejected from `children` inner join `case_steps_alerta` on `children`.`id` = `case_steps_alerta`.`child_id` inner join `tenants` on `children`.`tenant_id` = `tenants`.`id` where `children`.`deleted_at` is null and `children`.`tenant_id` = '{$tenantId}') as t2 where t1.tenant_id = t2.tenant_id"),
                );

                $cases = DB::select(
                    DB::raw("select tenants.uf, sum(case when `alert_status` = 'accepted' and `child_status` in ('out_of_school', 'in_observation') then 1 else 0 end) as '_in_progress', sum(case when `child_status` in ('in_school', 'in_observation') then 1 else 0 end) as '_enrollment', sum(case when `child_status` = 'in_school' then 1 else 0 end) as '_in_school', sum(case when `child_status` = 'in_observation' then 1 else 0 end) as '_in_observation', sum(case when `child_status` = 'out_of_school' and `alert_status` = 'accepted' then 1 else 0 end) as '_out_of_school', sum(case when `child_status` = 'cancelled' and `alert_status` = 'accepted' then 1 else 0 end) as '_cancelled', sum(case when `child_status` = 'transferred' then 1 else 0 end) as '_transferred', sum(case when `child_status` = 'interrupted' then 1 else 0 end) as '_interrupted', sum(case when (case_status = 'in_progress' or cancel_reason = 'city_transfer' or cancel_reason = 'justified_cancelled' or cancel_reason = 'death' or cancel_reason = 'not_found' or case_status in ('completed', 'interrupted', 'transferred')) and csr.deleted_at is null and cc.deleted_at is null and is_completed = 1 then 1 else 0 end) as '_enrollment_with_cancelled' from children inner join tenants on children.tenant_id = tenants.id left join children_cases cc on children.id = cc.child_id left join case_steps_rematricula csr on children.id = csr.child_id where children.deleted_at is null and tenants.id = '{$tenantId}'"),
                );

                $data = [];

                if ($alerts)
                    $data['alerts'] = [
                        'total' => $alerts[0]->accepted + $alerts[0]->pending + $alerts[0]->rejected,
                        'approved' => $alerts[0]->accepted,
                        'pending' => $alerts[0]->pending,
                        'rejected' => $alerts[0]->rejected
                    ];
                else
                    $data['alerts'] = [
                        'total' => 0,
                        'approved' => 0,
                        'pending' => 0,
                        'rejected' => 0
                    ];
                if ($cases)
                    $data['cases'] = [
                        'total' => $cases[0]->_in_school +
                            $cases[0]->_in_observation +
                            $cases[0]->_out_of_school +
                            $cases[0]->_cancelled +
                            $cases[0]->_transferred +
                            $cases[0]->_interrupted,
                        'in_progress' => $cases[0]->_in_progress,
                        'enrollment' => $cases[0]->_enrollment,
                        'in_school' => $cases[0]->_in_school,
                        'in_observation' => $cases[0]->_in_observation,
                        'out_of_school' => $cases[0]->_out_of_school,
                        'cancelled' => $cases[0]->_cancelled,
                        'transferred' => $cases[0]->_transferred,
                        'interrupted' => $cases[0]->_interrupted,
                        'enrollment_with_cancelled' => $cases[0]->_enrollment_with_cancelled
                    ];
                else
                    $data['cases'] = [
                        'total' => 0,
                        'in_progress' => 0,
                        'enrollment' => 0,
                        'in_school' => 0,
                        'in_observation' => 0,
                        'out_of_school' => 0,
                        'cancelled' => 0,
                        'transferred' => 0,
                        'interrupted' => 0,
                        'enrollment_with_cancelled' => 0
                    ];
                $data['causes_cases'] = $causes;
                $data['data_city'] = $data_city;
                return $data;
            });

            return response()->json(['status' => 'ok', 'stats' => $stats]);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    public function list_cities()
    {
        try {
            $uf = request('uf');
            $collection_cities = City::query()->where('uf', '=', $uf)->orderBy('name')->get(['name']);
            $cities = [];
            foreach ($collection_cities as $city) {
                array_push($cities, $city->name);
            }
            $data = [
                'cities_in_tenants' => $cities,
            ];

            return response()->json(['status' => 'ok', '_data' => $data]);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    public function report_by_dates()
    {

        $ibge_id = request('ibge_id');
        $initial_date = request('initial_date');
        $final_date = request('final_date');

        if (!$ibge_id || !$initial_date || !$final_date)
            return $this->api_failure('invalid_request');

        try {
            Carbon::parse($initial_date);
            Carbon::parse($final_date);
        } catch (\Exception $e) {
            return $this->api_failure('invalid_date');
        }

        $city = City::where('ibge_city_id', '=', $ibge_id)->get()->first();

        if (!$city)
            return $this->api_failure('invalid_city_id');

        $tenant = Tenant::where('city_id', '=', $city->id)->get()->first();

        if (!$tenant)
            return $this->api_failure('there_is_no_adhesion');

        $goal = $tenant->city->goal ? $tenant->city->goal->goal : null;

        //first_date

        $daily_justified = DB::table('daily_metrics_consolidated')
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as date, sum(justified_cancelled) as value"))
            ->where('tenant_id', '=', $tenant->id)
            ->where('date', '=', $initial_date)
            ->groupBy('date');

        $daily_justified_final = $daily_justified->get()->toArray();

        $daily_justified_final = array_map(function ($e) {
            $e->tipo = "Cancelamento após (re)matrícula";
            return $e;
        }, $daily_justified_final);

        $daily_enrollment = DB::table('daily_metrics_consolidated')
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as date, sum(in_observation)+sum(in_school) as value"))
            ->where('tenant_id', '=', $tenant->id)
            ->where('date', '=', $initial_date)
            ->groupBy('date');
        $daily_enrollment_final = $daily_enrollment->get()->toArray();

        $daily_enrollment_final = array_map(function ($e) {
            $e->tipo = "(Re)matrícula";
            return $e;
        }, $daily_enrollment_final);

        //----

        //last_date
        $daily_justified2 = DB::table('daily_metrics_consolidated')
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as date, sum(justified_cancelled) as value"))
            ->where('tenant_id', '=', $tenant->id)
            ->where('date', '=', $final_date)
            ->groupBy('date');

        $daily_justified_final2 = $daily_justified2->get()->toArray();

        $daily_justified_final2 = array_map(function ($e) {
            $e->tipo = "Cancelamento após (re)matrícula";
            return $e;
        }, $daily_justified_final2);

        $daily_enrollment2 = DB::table('daily_metrics_consolidated')
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as date, sum(in_observation)+sum(in_school) as value"))
            ->where('tenant_id', '=', $tenant->id)
            ->where('date', '=', $final_date)
            ->groupBy('date');
        $daily_enrollment_final2 = $daily_enrollment2->get()->toArray();

        $daily_enrollment_final2 = array_map(function ($e) {
            $e->tipo = "(Re)matrícula";
            return $e;
        }, $daily_enrollment_final2);

        return response()->json(
            [
                'goal' => $goal,
                'data' => [
                    'first_date' => array_merge($daily_enrollment_final, $daily_justified_final),
                    'last_date' => array_merge($daily_enrollment_final2, $daily_justified_final2)
                ]
            ]
        );
    }
    public function report_by_city()
    {

        $initial_date = request('initial_date');
        $final_date = request('final_date');

        if (!$initial_date || !$final_date)
            return $this->api_failure('invalid_request');

        try {
            Carbon::parse($initial_date);
            Carbon::parse($final_date);
        } catch (\Exception $e) {
            return $this->api_failure('invalid_date');
        }

        $goals = DB::table('goals')
            ->where('goal', '>', 0)
            ->select(['goals.id as id', 'cities.id as city_id', 'cities.name as city', 'goals.goal as second_measure', 'goals.goal_ciclo2 as first_measure', 'tenants.id as tenant_id'])
            ->join('cities', function ($join) {
                $join->on('goals.id', '=', 'cities.ibge_city_id');
            })
            ->join('tenants', function ($join) {
                $join->on('cities.id', '=', 'tenants.city_id')->where('tenants.is_active', '=', 1);
            })
            ->get();

        $goals = $goals->map(function ($goal) use ($initial_date, $final_date) {

            $first_data = DB::table('daily_metrics_consolidated')
                ->select(DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as date, justified_cancelled as cancelamento_apos_rematricula, in_observation+in_school as 'rematricula' "))
                ->where('tenant_id', '=', $goal->tenant_id)
                ->where('date', '=', $initial_date)->get()->first();

            $final_data = DB::table('daily_metrics_consolidated')
                ->select(DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as date, justified_cancelled as cancelamento_apos_rematricula, in_observation+in_school as 'rematricula' "))
                ->where('tenant_id', '=', $goal->tenant_id)
                ->where('date', '=', $final_date)->get()->first();

            $goal->first_date = $first_data;
            $goal->final_date = $final_data;

            return $goal;
        });

        return response()->json($goals);
    }
}
