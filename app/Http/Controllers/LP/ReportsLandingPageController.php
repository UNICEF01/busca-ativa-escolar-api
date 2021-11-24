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
            $keyOfCache = "report_" . ($typeOfCache === 'country' ? $typeOfCache : $typeOfCache . '_' . $resqueted[$typeOfCache]);
            $i = $typeOfCache === 'country' ? 21 : 18;
            $stat = Cache::get($keyOfCache);
            $stat = explode(" ", $stat);
            $causes = [];
            foreach (CaseCause::getAll() as $case) {
                //alerta pemanece com status de aceito se caso for cancelado!
                array_push($causes, ['id' => $case->id, 'cause' => $case->label, 'qtd' => $stat[$i++]]);
            }
            if ($typeOfCache === 'country') {
                $stats =  [
                    'ufs' => [
                        "num_ufs" => intval($stat[0]),
                    ],
                    'tenants' => [
                        "num_tenants" => intval($stat[2]),
                        "active" => intval($stat[3]),
                        "inactive" => intval($stat[4]),
                        "num_signups" => intval($stat[5]),
                        "num_pending_setup" => intval($stat[6]),
                    ],
                    'alerts' => [

                        "_approved" => intval($stat[9]),
                        "_pending" => intval($stat[10]),
                        "_rejected" => intval($stat[11]),
                    ],
                    'cases' => [
                        '_enrollment' => intval($stat[14]),
                        '_in_school' => intval($stat[15]),
                        '_transferred' => intval($stat[19]),
                        '_in_observation' => intval($stat[16]),
                        '_out_of_school' => intval($stat[17]),
                        '_cancelled' => intval($stat[18]),
                        '_interrupted' => intval($stat[20]),
                    ],
                    'causes_cases' => $causes
                ];
            } else {
                $stats =  [
                    'tenants' => [
                        'is_approved' => intval($stat[0]),
                        'num_tenants' => intval($stat[1]),
                        'active' => intval($stat[5]),
                        'inactive' => intval($stat[6]),
                        'num_pending_setup' => intval($stat[4]),
                    ],
                    'alerts' => [

                        '_approved' => intval($stat[8]),

                        '_pending' => intval($stat[9]),

                        '_rejected' => intval($stat[10]),
                    ],
                    'cases' => [
                        '_enrollment' => intval($stat[11]),
                        '_in_school' => intval($stat[12]),
                        '_transferred' => intval($stat[13]),
                        '_in_observation' => intval($stat[14]),
                        '_out_of_school' => intval($stat[15]),
                        '_cancelled' => intval($stat[16]),
                        '_interrupted' => intval($stat[17]),
                    ],
                    'causes_cases' => $causes
                ];
            }
            return response()->json(['status' => 'ok', '_data' => $stats]);
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

                return [

                    'alerts' => [
                        '_approved' =>

                        \DB::table('case_steps_alerta')
                            ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                            ->where(
                                [
                                    ['case_steps_alerta.tenant_id', $tenantId],
                                    ['case_steps_alerta.alert_status', 'accepted'],
                                    ['children.alert_status', 'accepted'],
                                ]
                            )
                            ->count(),

                        '_pending' =>

                        \DB::table('case_steps_alerta')
                            ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                            ->where(
                                [
                                    ['case_steps_alerta.tenant_id', $tenantId],
                                    ['case_steps_alerta.alert_status', 'pending'],
                                ]
                            )
                            ->count(),

                        '_rejected' =>

                        \DB::table('case_steps_alerta')
                            ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                            ->where(
                                [
                                    ['case_steps_alerta.tenant_id', $tenantId],
                                    ['children.alert_status', 'rejected']
                                ]
                            )
                            ->count(),
                    ],

                    'cases' => [

                        '_out_of_school' =>

                        \DB::table('case_steps_alerta')
                            ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                            ->join('children_cases', 'children_cases.child_id', '=', 'children.id')
                            ->where(
                                [
                                    ['case_steps_alerta.tenant_id', $tenantId],
                                    ['case_steps_alerta.alert_status', 'accepted'],
                                    ['children.alert_status', 'accepted'],
                                    ['children_cases.case_status', 'in_progress'],
                                    ['children.child_status', '=', 'out_of_school']
                                ]
                            )->count(),

                        '_cancelled' =>

                        \DB::table('case_steps_alerta')
                            ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                            ->join('children_cases', 'children_cases.child_id', '=', 'children.id')
                            ->where(
                                [
                                    ['case_steps_alerta.tenant_id', $tenantId],
                                    ['case_steps_alerta.alert_status', 'accepted'],
                                    ['children.alert_status', 'accepted'],
                                    ['children.child_status', 'cancelled'],
                                    ['children_cases.case_status', 'cancelled']
                                ]
                            )->count(),

                        '_in_school' =>

                        \DB::table('case_steps_alerta')
                            ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                            ->join('children_cases', 'children_cases.child_id', '=', 'case_steps_alerta.child_id')
                            ->where(
                                [
                                    ['case_steps_alerta.tenant_id', $tenantId],
                                    ['case_steps_alerta.alert_status', 'accepted'],
                                    ['children.alert_status', 'accepted'],
                                    ['children.child_status', 'in_school'],
                                    ['children_cases.case_status', 'completed']
                                ]
                            )->count(),
                        '_transferred' =>

                        \DB::table('case_steps_alerta')
                            ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                            ->join('children_cases', 'children_cases.child_id', '=', 'case_steps_alerta.child_id')
                            ->where(
                                [
                                    ['case_steps_alerta.tenant_id', $tenantId],
                                    ['case_steps_alerta.alert_status', 'accepted'],
                                    ['children.alert_status', 'accepted'],
                                    ['children.child_status', 'transferred'],
                                    ['children_cases.case_status', 'completed']
                                ]
                            )->count(),

                        '_interrupted' =>

                        \DB::table('children')
                            ->join('case_steps_alerta', 'children.id', '=', 'case_steps_alerta.child_id')
                            ->join('children_cases', 'children_cases.child_id', '=', 'case_steps_alerta.child_id')
                            ->where(
                                [
                                    ['case_steps_alerta.tenant_id', $tenantId],
                                    ['case_steps_alerta.alert_status', 'accepted'],
                                    ['children.alert_status', 'accepted'],
                                    ['children.child_status', 'interrupted'],
                                    ['children_cases.case_status', 'interrupted']
                                ]
                            )->count(),

                        '_transferred' =>

                        \DB::table('children')
                            ->join('case_steps_alerta', 'children.id', '=', 'case_steps_alerta.child_id')
                            ->join('children_cases', 'children_cases.child_id', '=', 'case_steps_alerta.child_id')
                            ->where(
                                [
                                    ['case_steps_alerta.tenant_id', $tenantId],
                                    ['case_steps_alerta.alert_status', 'accepted'],
                                    ['children.alert_status', 'accepted'],
                                    ['children.child_status', 'transferred'],
                                    ['children_cases.case_status', 'transferred']
                                ]
                            )->count(),

                        '_in_observation' =>

                        \DB::table('case_steps_alerta')
                            ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                            ->join('children_cases', 'children_cases.child_id', '=', 'case_steps_alerta.child_id')
                            ->where(
                                [
                                    ['case_steps_alerta.tenant_id', $tenantId],
                                    ['case_steps_alerta.alert_status', 'accepted'],
                                    ['children.alert_status', 'accepted'],
                                    ['children.child_status', 'in_observation'],
                                    ['children_cases.case_status', 'in_progress']
                                ]
                            )->count(),
                        '_out_of_school' =>

                        \DB::table('case_steps_alerta')
                            ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                            ->join('children_cases', 'children_cases.child_id', '=', 'case_steps_alerta.child_id')
                            ->where(
                                [
                                    ['case_steps_alerta.tenant_id', $tenantId],
                                    // ['case_steps_alerta.alert_status', 'accepted'],
                                    ['children.alert_status', 'accepted'],
                                    ['children.child_status', 'out_of_school'],
                                    ['children_cases.case_status', 'in_progress']
                                ]
                            )->count(),

                    ],

                    'causes_cases' => $causes,

                    'data_city' => $data_city

                ];
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
