<?php

namespace BuscaAtivaEscolar\Http\Controllers\Bar;

use Auth;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\Goal;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\CaseSteps\Rematricula;
use BuscaAtivaEscolar\MapCountry;
use BuscaAtivaEscolar\Tenant;
use DB;
use Illuminate\Http\Request;
use BuscaAtivaEscolar\MapState;

class ReportsBar extends BaseController
{

    const SELO_UNICEF_PARTICIPA = "PARTICIPA DO SELO UNICEF";
    const SELO_UNICEF_NAO_PARTICIPA = "NÃO PARTICIPA DO SELO UNICEF";
    const SELO_UNICEF_TODOS = "TODOS";

    public function city_bar()
    {

        if ($this->currentUser()->tenant == null) {
            return response()->json(['status' => 'no_tenant']);
        }

        try {

            $report = [

                'bar' => [

                    'registered_at' => $this->currentUser()->tenant->registered_at->toDateTimeString(),

                    'config' => [
                        'is_configured' => $this->currentUser()->tenant->settings ? true : false,
                        'updated_at' => $this->currentUser()->tenant->settings ? $this->currentUser()->tenant->updated_at->toDateTimeString() : null
                    ],

                    'first_alert' =>

                    DB::table('children')
                        ->join('case_steps_alerta', 'children.id', '=', 'case_steps_alerta.child_id')
                        ->where('case_steps_alerta.tenant_id', '=', $this->currentUser()->tenant->id)
                        ->orderBy('case_steps_alerta.created_at', 'asc')
                        ->first()->created_at ?? null,

                    'first_case' =>

                    DB::table('children')
                        ->join('case_steps_alerta', 'children.id', '=', 'case_steps_alerta.child_id')
                        ->where(
                            [
                                'case_steps_alerta.tenant_id' => $this->currentUser()->tenant->id,
                                'case_steps_alerta.is_completed' => true
                            ]
                        )
                        ->orderBy('case_steps_alerta.completed_at', 'asc')
                        ->first()->completed_at ?? null,

                    'first_reinsertion_class' =>

                    DB::table('children')
                        ->join('case_steps_rematricula', 'children.id', '=', 'case_steps_rematricula.child_id')
                        ->where(
                            [
                                'case_steps_rematricula.tenant_id' => $this->currentUser()->tenant->id,
                                'case_steps_rematricula.is_completed' => true
                            ]
                        )
                        ->orderBy('case_steps_rematricula.completed_at', 'asc')
                        ->first()->completed_at ?? null,

                ],

                'alert_box' => [

                    'alerts_created' =>
                    DB::table('case_steps_alerta')
                        ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                        ->where('children.tenant_id', '=', $this->currentUser()->tenant->id)
                        ->count(),

                    'alerts_accepted' =>
                    DB::table('case_steps_alerta')
                        ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                        ->where('case_steps_alerta.tenant_id', '=', $this->currentUser()->tenant->id)
                        ->where('children.alert_status', '=', Child::ALERT_STATUS_ACCEPTED)
                        ->count(),

                    'alerts_pending' =>
                    DB::table('case_steps_alerta')
                        ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                        ->where('case_steps_alerta.tenant_id', '=', $this->currentUser()->tenant->id)
                        ->where('children.alert_status', '=', Child::ALERT_STATUS_PENDING)
                        ->count(),

                    'alerts_rejected' =>
                    DB::table('case_steps_alerta')
                        ->join('children', 'children.id', '=', 'case_steps_alerta.child_id')
                        ->where('case_steps_alerta.tenant_id', '=', $this->currentUser()->tenant->id)
                        ->where('children.alert_status', '=', Child::ALERT_STATUS_REJECTED)
                        ->count(),

                ],

                'cases_box' => [

                    'cases_on_time' =>
                    Child::whereHas('cases', function ($query) {
                        $query->where(['case_status' => 'in_progress']);
                    })->where(
                        [
                            'tenant_id' => $this->currentUser()->tenant->id,
                            'deadline_status' => Child::DEADLINE_STATUS_NORMAL,
                            'alert_status' => Child::ALERT_STATUS_ACCEPTED
                        ]
                    )->count(),
                    'steps_on_time' => [

                        'pesquisa' =>

                        Child::whereHas('cases', function ($query) {
                            $query->where(['case_status' => 'in_progress']);
                        })->where(
                            [
                                'tenant_id' => $this->currentUser()->tenant->id,
                                'deadline_status' => Child::DEADLINE_STATUS_NORMAL,
                                'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                                'current_step_type' => "BuscaAtivaEscolar\CaseSteps\Pesquisa"
                            ]
                        )->count(),

                        'analise_tecnica' =>

                        Child::whereHas('cases', function ($query) {
                            $query->where(['case_status' => 'in_progress']);
                        })->where(
                            [
                                'tenant_id' => $this->currentUser()->tenant->id,
                                'deadline_status' => Child::DEADLINE_STATUS_NORMAL,
                                'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                                'current_step_type' => "BuscaAtivaEscolar\CaseSteps\AnaliseTecnica"
                            ]
                        )->count(),

                        'gestao_do_caso' =>

                        Child::whereHas('cases', function ($query) {
                            $query->where(['case_status' => 'in_progress']);
                        })->where(
                            [
                                'tenant_id' => $this->currentUser()->tenant->id,
                                'deadline_status' => Child::DEADLINE_STATUS_NORMAL,
                                'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                                'current_step_type' => "BuscaAtivaEscolar\CaseSteps\GestaoDoCaso"
                            ]
                        )->count(),

                        'rematricula' =>

                        Child::whereHas('cases', function ($query) {
                            $query->where(['case_status' => 'in_progress']);
                        })->where(
                            [
                                'tenant_id' => $this->currentUser()->tenant->id,
                                'deadline_status' => Child::DEADLINE_STATUS_NORMAL,
                                'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                                'current_step_type' => "BuscaAtivaEscolar\CaseSteps\Rematricula"
                            ]
                        )->count(),

                        'observacao' =>

                        Child::whereHas('cases', function ($query) {
                            $query->where(['case_status' => 'in_progress']);
                        })->where(
                            [
                                'tenant_id' => $this->currentUser()->tenant->id,
                                'deadline_status' => Child::DEADLINE_STATUS_NORMAL,
                                'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                                'current_step_type' => "BuscaAtivaEscolar\CaseSteps\Observacao"
                            ]
                        )->count(),
                    ],

                    'cases_late' =>
                    Child::whereHas('cases', function ($query) {
                        $query->where(['case_status' => 'in_progress']);
                    })->where(
                        [
                            'tenant_id' => $this->currentUser()->tenant->id,
                            'deadline_status' => Child::DEADLINE_STATUS_LATE,
                            'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                        ]
                    )->count(),

                    'steps_late' => [
                        'pesquisa' =>

                        Child::has('cases')->where(
                            [
                                'tenant_id' => $this->currentUser()->tenant->id,
                                'deadline_status' => Child::DEADLINE_STATUS_LATE,
                                'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                                'current_step_type' => "BuscaAtivaEscolar\CaseSteps\Pesquisa"
                            ]
                        )->count(),

                        'analise_tecnica' =>

                        Child::has('cases')->where(
                            [
                                'tenant_id' => $this->currentUser()->tenant->id,
                                'deadline_status' => Child::DEADLINE_STATUS_LATE,
                                'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                                'current_step_type' => "BuscaAtivaEscolar\CaseSteps\AnaliseTecnica"
                            ]
                        )->count(),

                        'gestao_do_caso' =>

                        Child::has('cases')->where(
                            [
                                'tenant_id' => $this->currentUser()->tenant->id,
                                'deadline_status' => Child::DEADLINE_STATUS_LATE,
                                'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                                'current_step_type' => "BuscaAtivaEscolar\CaseSteps\GestaoDoCaso"
                            ]
                        )->count(),

                        'rematricula' =>

                        Child::has('cases')->where(
                            [
                                'tenant_id' => $this->currentUser()->tenant->id,
                                'deadline_status' => Child::DEADLINE_STATUS_LATE,
                                'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                                'current_step_type' => "BuscaAtivaEscolar\CaseSteps\Rematricula"
                            ]
                        )->count(),

                        'observacao' =>

                        Child::has('cases')->where(
                            [
                                'tenant_id' => $this->currentUser()->tenant->id,
                                'deadline_status' => Child::DEADLINE_STATUS_LATE,
                                'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                                'current_step_type' => "BuscaAtivaEscolar\CaseSteps\Observacao",
                                'child_status' => "in_observation"
                            ]
                        )->count(),
                    ],

                ],

                'goal_box' => [

                    'goal' => $this->currentUser()->tenant->city->goal ? $this->currentUser()->tenant->city->goal->goal : null,
                    'goal_ciclo1' => $this->currentUser()->tenant->city->goal ? $this->currentUser()->tenant->city->goal->goal_ciclo1 : null,
                    'accumulated_ciclo1' => $this->currentUser()->tenant->city->goal ? $this->currentUser()->tenant->city->goal->accumulated_ciclo1 : null,

                    'reinsertions_classes' =>
                    Rematricula::whereHas('cases', function ($query) {
                        $query->where(['case_status' => 'in_progress'])
                            ->orWhere(['cancel_reason' => 'city_transfer'])
                            ->orWhere(['cancel_reason' => 'death'])
                            ->orWhere(['cancel_reason' => 'not_found'])
                            ->orWhere(['case_status' => 'completed'])
                            ->orWhere(['case_status' => 'interrupted'])
                            ->orWhere(['case_status' => 'transferred']);
                    })->where(
                        [
                            'tenant_id' => $this->currentUser()->tenant->id,
                            'is_completed' => true
                        ]
                    )
                        ->orderBy('completed_at', 'asc')
                        ->count(),

                    'observations' => $this->getObseravtionsValues($this->currentUser()->tenant)
                ]
            ];

            return response()->json($report);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    private function getObseravtionsValues($tenant)
    {
        $qtd_observations_1 = 0;
        $qtd_observations_2 = 0;
        $qtd_observations_3 = 0;
        $qtd_observations_4 = 0;

        $children = Child::has('cases')->where(
            [
                'tenant_id' => $tenant->id,
                'alert_status' => Child::ALERT_STATUS_ACCEPTED,
                'current_step_type' => 'BuscaAtivaEscolar\CaseSteps\Observacao',
                'child_status' => Child::STATUS_OBSERVATION
            ]
        )->get();

        foreach ($children as $child) {

            switch ($child->currentStep->step_index) {
                case 60:
                    $qtd_observations_1++;
                    break;
                case 70:
                    $qtd_observations_2++;
                    break;
                case 80:
                    $qtd_observations_3++;
                    break;
                case 90:
                    $qtd_observations_4++;
                    break;
            }
        }

        return [
            'qtd_observations_1' => $qtd_observations_1,
            'qtd_observations_2' => $qtd_observations_2,
            'qtd_observations_3' => $qtd_observations_3,
            'qtd_observations_4' => $qtd_observations_4,
        ];
    }

    public function getDataRematriculaDaily(Request $request)
    {

        $uf = $request->input('uf') ?? null;
        $tenantId = $request->input('tenant_id') ?? null;
        $selo = $request->input('selo') ?? null;


        //cancelamentos -----------------------------------------------

        $daily_justified = DB::table('daily_metrics_consolidated')
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as date, sum(justified_cancelled) as value"))
            ->groupBy('date');

        if (Auth::user()->isRestrictedToTenant()) {
            $daily_justified->where('tenant_id', '=', Auth::user()->tenant->id);
        }

        if (Auth::user()->isRestrictedToUF()) {
            $daily_justified->where('state', '=', Auth::user()->uf);
        }

        if ($uf != null and $uf != "null") {
            $daily_justified->where('state', '=', $uf);
        }

        if ($tenantId != null and $tenantId != "null") {
            $daily_justified->where('tenant_id', '=', $tenantId);
        }

        if ($selo == self::SELO_UNICEF_PARTICIPA) {
            $daily_justified->where(function ($q) {
                $q->where('selo', '=', 1);
            });
        }

        if ($selo == self::SELO_UNICEF_NAO_PARTICIPA) {
            $daily_justified->where(function ($q) {
                $q->where('selo', '=', 0);
            });
        }

        $daily_justified_final = $daily_justified->get()->toArray();

        $daily_justified_final = array_map(function ($e) {
            $e->tipo = "Cancelamento após (re)matrícula";
            return $e;
        }, $daily_justified_final);

        //cancelamentos -----------------------------------------------



        //(re)matricula -----------------------------------------------

        $daily_enrollment = DB::table('daily_metrics_consolidated')
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as date, sum(in_observation)+sum(in_school) as value"))
            ->groupBy('date');

        if (Auth::user()->isRestrictedToTenant()) {
            $daily_enrollment->where('tenant_id', '=', Auth::user()->tenant->id);
        }

        if (Auth::user()->isRestrictedToUF()) {
            $daily_enrollment->where('state', '=', Auth::user()->uf);
        }

        if ($uf != null and $uf != "null") {
            $daily_enrollment->where('state', '=', $uf);
        }

        if ($tenantId != null and $tenantId != "null") {
            $daily_enrollment->where('tenant_id', '=', $tenantId);
        }

        if ($selo == self::SELO_UNICEF_PARTICIPA) {
            $daily_enrollment->where(function ($q) {
                $q->where('selo', '=', 1);
            });
        }

        if ($selo == self::SELO_UNICEF_NAO_PARTICIPA) {
            $daily_enrollment->where(function ($q) {
                $q->where('selo', '=', 0);
            });
        }

        $daily_enrollment_final = $daily_enrollment->get()->toArray();

        $daily_enrollment_final = array_map(function ($e) {
            $e->tipo = "(Re)matrícula";
            return $e;
        }, $daily_enrollment_final);

        //(re)matricula -----------------------------------------------



        //meta --------------------------------------------------------

        if (Auth::user()->isRestrictedToTenant()) {
            $goal_final = Auth::user()->tenant->city->goal ? $this->currentUser()->tenant->city->goal->accumulated_ciclo1 + $this->currentUser()->tenant->city->goal->goal : 0;
        }

        if (Auth::user()->isRestrictedToUF()) {

            $goal = Goal::selectRaw('sum(goals.goal) as goals')
                ->join('cities', 'cities.ibge_city_id', '=', 'goals.id')
                ->join('tenants', 'tenants.city_id', '=', 'cities.id')
                ->where([
                    ['cities.uf', '=', Auth::user()->uf]
                ]);

            if ($tenantId != null and $tenantId != "null") {
                $goal->where([
                    ['cities.uf', '=', Auth::user()->uf],
                    ['tenants.id', '=', $tenantId]
                ]);
            }

            $goal_final = $goal->get()->toArray()[0]['goals'];
        }

        if (Auth::user()->isGlobal()) {

            $goal = Goal::selectRaw('sum(goals.goal) as goals')
                ->join('cities', 'cities.ibge_city_id', '=', 'goals.id')
                ->join('tenants', 'tenants.city_id', '=', 'cities.id');

            if ($uf != null and $uf != "null") {
                $goal->where([
                    ['cities.uf', '=', $uf]
                ]);
            }

            if ($tenantId != null and $tenantId != "null") {
                $goal->where([
                    ['cities.uf', '=', $uf],
                    ['tenants.id', '=', $tenantId]
                ]);
            }

            $goal_final = $goal->get()->toArray()[0]['goals'];
        }

        return response()->json(
            [
                'goal' => $goal_final,
                'data' => array_merge($daily_enrollment_final, $daily_justified_final),
                'selo' => $selo
            ]
        );
    }

    public function ufsBySelo()
    {

        $selo = request('selo');

        if ($selo == self::SELO_UNICEF_PARTICIPA) {
            $ufs = City::select('uf')->has('goal')->orderBy('uf', 'asc')->groupBy('uf')->get();
        }

        if ($selo == self::SELO_UNICEF_NAO_PARTICIPA) {

            $cities_selo_array = City::select('id')->has('goal')->get()->toArray();
            $ufs = Tenant::select('uf')->whereNotIn('city_id', $cities_selo_array)->orderBy('uf', 'asc')->groupBy('uf')->get();
        }

        if ($selo == self::SELO_UNICEF_TODOS) {
            $ufs = Tenant::select('uf')->orderBy('uf', 'asc')->groupBy('uf')->get();
        }

        $data = [
            'selo' => $selo,
            'data' => $ufs,
        ];

        return response()->json($data);
    }

    public function tenantsBySelo()
    {

        $selo = request('selo');
        $uf = request('uf');

        if ($selo == self::SELO_UNICEF_PARTICIPA) {
            $tenants = Tenant::whereHas('city', function ($q) {
                $q->whereHas('goal');
            })->where('uf', '=', $uf)->orderBy('name', 'asc')->get();
        }

        if ($selo == self::SELO_UNICEF_NAO_PARTICIPA) {
            $tenants = Tenant::whereHas('city', function ($q) {
                $q->doesntHave('goal');
            })->where('uf', '=', $uf)->orderBy('name', 'asc')->get();
        }

        if ($selo == self::SELO_UNICEF_TODOS) {
            $tenants = Tenant::where('uf', '=', $uf)->orderBy('name', 'asc')->get();
        }

        $data = [
            'selo' => $selo,
            'data' => $tenants
        ];

        return response()->json($data);
    }

    public function getDataMapFusionChart(Request $request)
    {

           $uf = request('uf');

        $states = [
            'AC' => ['001', 'Acre'],
            'AL' => ['002', 'Alagoas'],
            'AP' => ['003', 'Amapa'],
            'AM' => ['004', 'Amazonas'],
            'BA' => ['005', 'Bahia'],
            'CE' => ['006', 'Ceara'],
            'DF' => ['007', 'Distrito Federal'],
            'ES' => ['008', 'Espirito Santo'],
            'GO' => ['009', 'Goias'],
            'MA' => ['010', 'Maranhao'],
            'MT' => ['011', 'Mato Grosso'],
            'MS' => ['012', 'Mato Grosso do Sul'],
            'MG' => ['013', 'Minas Gerais'],
            'PA' => ['014', 'Para'],
            'PB' => ['015', 'Paraiba'],
            'PR' => ['016', 'Parana'],
            'PE' => ['017', 'Pernambuco'],
            'PI' => ['018', 'Piaui'],
            'RJ' => ['019', 'Rio de Janeiro'],
            'RN' => ['020', 'Rio Grande do Norte'],
            'RS' => ['021', 'Rio Grande do Sul'],
            'RO' => ['022', 'Rondonia'],
            'RR' => ['023', 'Roraima'],
            'SC' => ['024', 'Santa Catarina'],
            'SP' => ['025', 'Sao Paulo'],
            'SE' => ['026', 'Sergipe'],
            'TO' => ['027', 'Tocantins']
        ];

        if ($uf != null and $uf != "null") {
            $stats = \Cache::get('map_state_' . $uf);
            $stats = explode("-", $stats);
            $size = count($stats) - 1;
            $data = [];
            $all_values = [];
            $i = 0;
            foreach ($stats as $stat) {
                if ($i < $size) {
                    array_push($all_values, explode(" ", $stat)[1]);
                    $name = explode(" ", $stat)[2];
                    $data[$i++] = [
                        "id" => explode(" ", $stat)[0],
                        "value" => explode(" ", $stat)[1],
                        "name_city" => utf8_encode(explode(" ", $stat)[2]),
                        "showLabel" => 0,
                    ];
                }
            }
        } else {
            $stats = \Cache::get('map_country');
            $stats = explode("-", $stats);
            $data = [];
            $all_values = [];
            $i = 0;
            foreach ($stats as $stat) {
                if ($i < 26) {
                    array_push($all_values, explode(" ", $stat)[1]);
                    $name = explode(" ", $stat)[0];
                    $data[$i++] = [
                        "place_uf" => $name,
                        "value" => explode(" ", $stat)[1],
                        "id" => $states[$name][0],
                        "displayValue" => $name,
                        "showLabel" => 1,
                        "simple_name" => strtolower($name)
                    ];
                }
            }
        }

        $final_data = [
            'colors' => [
                [
                    "maxvalue" => explode(" ", $stats[0])[1] > 0 ? explode(" ", $stats[0])[1] : 0,
                    "code" => "#e44a00"
                ],
                [
                    "maxvalue" => count($all_values) > 0 ? max($all_values) / 2 : 0,
                    "code" => "#f8bd19"
                ]
            ],
            'data' => $data
        ];

        return response()->json($final_data);
    }
}
