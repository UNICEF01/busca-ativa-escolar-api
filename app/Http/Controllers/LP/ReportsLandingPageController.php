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
                if (!empty($value)) $typeOfCache = $key;
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
            $tenant = Tenant::where([['city_id', '=', $city_ibge->id], ['is_active', '=', 1]])->withTrashed()->first();
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
            return response()->json(['status' => 'ok', '_data' => $data]);
        }

        try {

            $stats = Cache::remember('report_city_' . $tenantId, 86400, function () use ($tenantId, $data_city) {
                $causes = [];

                foreach (CaseCause::getAll() as $case) {

                    //alerta pemanece com status de aceito se caso for cancelado!
                    $qtd =
                        \DB::table('children')
                        ->join('case_steps_pesquisa', 'children.id', '=', 'case_steps_pesquisa.child_id')
                        ->where(
                            [
                                ['case_steps_pesquisa.tenant_id', $tenantId],
                                ['case_steps_pesquisa.case_cause_ids', 'like', "%{$case->id}%"],
                                ['children.alert_status', 'accepted']
                            ]
                        )
                        ->count();

                    if ($qtd > 0) {
                        array_push($causes, ['id' => $case->id, 'cause' => $case->label, 'qtd' => $qtd]);
                    }
                }
                $alerts = DB::select(
                    DB::raw("select t2.accepted, t1.pending, t2.rejected from(select `children`.`tenant_id`, count(1) as pending from `children` inner join `tenants` on `children`.`tenant_id` = `tenants`.`id` where exists(select count(1) from `case_steps_alerta` where `children`.`id` = `case_steps_alerta`.`child_id` and `alert_status` = 'pending' and `case_steps_alerta`.`deleted_at` is null) and `alert_status` = 'pending' and `children`.`deleted_at` is null and `children`.`tenant_id` = '{$tenantId}') as t1, (select `children`.`tenant_id`, sum(case when `case_steps_alerta`.`alert_status` = 'accepted' and `children`.`alert_status` = 'accepted' then 1 else 0 end) as accepted, sum(case when `children`.`alert_status` = 'rejected' then 1 else 0 end) as rejected from `children` inner join `case_steps_alerta` on `children`.`id` = `case_steps_alerta`.`child_id` inner join `tenants` on `children`.`tenant_id` = `tenants`.`id` where `children`.`deleted_at` is null and `children`.`tenant_id` = '{$tenantId}') as t2 where t1.tenant_id = t2.tenant_id"),
                );

                $cases = DB::select(
                    DB::raw("select tenants.uf,sum(case when `alert_status` = 'accepted' and `child_status` in ('out_of_school', 'in_observation') then 1 else 0 end) as '_in_progress',sum(case when `child_status` in ('in_school', 'in_observation') then 1 else 0 end) as '_enrollment',sum(case when `child_status` = 'in_school' then 1 else 0 end) as '_in_school',sum(case when `child_status` = 'in_observation' then 1 else 0 end) as '_in_observation',sum(case when `child_status` = 'out_of_school' and `alert_status` = 'accepted' then 1 else 0 end) as '_out_of_school',sum(case when `child_status` = 'cancelled' and `alert_status` = 'accepted' then 1 else 0 end) as '_cancelled',sum(case when `child_status` = 'transferred' then 1 else 0 end) as '_transferred',sum(case when `child_status` = 'interrupted' then 1 else 0 end) as '_interrupted' from children inner join tenants on children.tenant_id  = tenants.id where children.deleted_at is null and tenants.id = '{$tenantId}'"),
                );

                $data = [];

                if (array_key_exists(0, $alerts))
                    $data['alerts'] = [
                        '_total' => $alerts[0]->accepted + $alerts[0]->pending + $alerts[0]->rejected,
                        '_approved' => $alerts[0]->accepted,
                        '_pending' => $alerts[0]->pending,
                        '_rejected' => $alerts[0]->rejected
                    ];
                else
                    $data['alerts'] = [
                        '_total' => 0,
                        '_approved' => 0,
                        '_pending' => 0,
                        '_rejected' => 0
                    ];
                if (array_key_exists(0, $cases))
                    $data['cases'] = [
                        '_total' => $cases[0]->_in_school +
                            $cases[0]->_in_observation +
                            $cases[0]->_out_of_school +
                            $cases[0]->_cancelled +
                            $cases[0]->_transferred +
                            $cases[0]->_interrupted,
                        '_in_progress' => $cases[0]->_in_progress,
                        '_enrollment' => $cases[0]->_enrollment,
                        '_in_school' => $cases[0]->_in_school,
                        '_in_observation' => $cases[0]->_in_observation,
                        '_out_of_school' => $cases[0]->_out_of_school,
                        '_cancelled' => $cases[0]->_cancelled,
                        '_transferred' => $cases[0]->_transferred,
                        '_interrupted' => $cases[0]->_interrupted
                    ];
                else
                    $data['cases'] = [
                        '_total' => 0,
                        '_in_progress' => 0,
                        '_enrollment' => 0,
                        '_in_school' => 0,
                        '_in_observation' => 0,
                        '_out_of_school' => 0,
                        '_cancelled' => 0,
                        '_transferred' => 0,
                        '_interrupted' => 0
                    ];
                $data['causes_cases'] = $causes;
                $data['data_city'] = $data_city;
                return $data;
            });
            return response()->json(['status' => 'ok', '_data' => $stats]);
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
}
