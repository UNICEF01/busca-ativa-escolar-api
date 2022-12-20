<?php

namespace BuscaAtivaEscolar\Cache;

use Cache;
use BuscaAtivaEscolar\Data\CaseCause;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\City;
use DB;
use Auth;
use BuscaAtivaEscolar\Http\Controllers\BaseController;

class CacheService extends BaseController
{

    public function search(array $arr, int $n, string $target): int
    {
        if ($arr[$n - 1] == $target)
            return $n - 1;

        $backup = $arr[$n - 1];
        $arr[$n - 1] = $target;

        for ($i = 0;; $i++) {
            if ($arr[$i] == $target) {
                $arr[$n - 1] = $backup;
                if ($i < $n - 1)
                    return $i;
                return -1;
            }
        }
    }

    public function returnData(string $searched, bool $check = false): array
    {
        $reg = ['N', 'NE', 'CO', 'SD', 'S', 'BR'];
        $searched = $searched == 'BR' ? '' : $searched;
        $reason_causes = [];
        if (!in_array($searched, $reg)) {
            $tenantIDs = Tenant::getIDsWithinUF($searched);
            $cityIDs = City::getIDsWithinUF($searched);
        }
        $data = [];
        $ufs = Cache::get("state_signup_" . $searched);
        $tenants_signup = explode(" ", Cache::get("tenant_signup_" . $searched));
        $tenants = explode(" ", Cache::get("tenants_" . $searched));
        $alerts = explode(" ", Cache::get("alerts_" . $searched));
        $causes = explode(" ", Cache::get("cases_" . $searched));
        $reasons = explode(" ", Cache::get("reason_cases_" . $searched));
        $i = 0;
        
        foreach (CaseCause::getAll() as $case) {
            array_push($reason_causes, ['id' => $case->id, 'cause' => $case->label, 'qtd' => $reasons[$i++]]);
        }
        
        $data  = [
            "ufs" => [
                "is_approved" => "$ufs",
                "num_ufs" => "$ufs",
                "num_pending_state_signups" => "0",
            ],
            "tenants" => [
                "num_tenants" => "$tenants[0]",
                "active" => "$tenants[1]",
                "inactive" => "$tenants[2]",
                "num_signups" => '' . ($tenants[0] + $tenants_signup[1]) . '',
                "num_pending_setup" => "$tenants_signup[1]",
                "num_pending_signups" => "$tenants_signup[2]"
            ],
            "alerts" => [
                "_total" => '' . $alerts[0] + $alerts[1] + $alerts[2] . '',
                "_approved" => "$alerts[0]",
                "_pending" => "$alerts[1]",
                "_rejected" => "$alerts[2]"
            ],
            "cases" => [
                '_total' => '' . $causes[2] + $causes[3] + $causes[4] + $causes[5] + $causes[6] + $causes[7] . '',
                '_in_progress' => "$causes[0]",
                '_enrollment' => "$causes[1]",
                '_in_school' => "$causes[2]",
                '_in_observation' => "$causes[3]",
                '_out_of_school' => "$causes[4]",
                '_cancelled' => "$causes[5]",
                '_transferred' => "$causes[6]",
                '_interrupted' => "$causes[7]",
            ],
            'causes_cases' => $reason_causes,
            'tenant_ids' => $check ? $tenantIDs : '',
            'city_ids' => $check ? $cityIDs : ''
        ];
        return $data;
    }

    public function binarySerchString(array $arr, string $target): int
    {
        $l = 0;
        $r = count($arr) - 1;

        while ($l <= $r) {
            $m = $l + (int)(($r - $l) / 2);
            $res = strcmp($target, $arr[$m]);

            if ($res == 0)
                return $m;

            else if ($res > 0)
                $l = $m + 1;

            else
                $r = $m - 1;
        }

        return -1;
    }

    public function returnMap($uf)
    {

        $ufs = [
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
        $data = [];
        $all_values = [];
        $j = 0;
        if ($uf != null and $uf != "null") {
            $key = "map_" . $uf;
            $cache = Cache::get($key);
            $cache = explode("\n", $cache);
            for ($i = 0; $i < count($cache) - 2; ++$i) {
                $result = explode("-", $cache[$i]);
                $name = utf8_encode(trim($result[1]));
                array_push($all_values, (int)trim($result[2]));
                $data[$j++] = [
                    "id" => trim($result[0]),
                    "value" => trim($result[2]),
                    "name_city" => $name,
                    "showLabel" => 0,
                ];
            }
        } else {
            $cache = Cache::get("map_BR");
            $cache = explode("\n", $cache);
            for ($i = 0; $i < count($cache) - 1; ++$i) {
                $result = explode("-", $cache[$i]);
                $name = trim($result[0]);
                if ($result[1] > 0) {
                    array_push($all_values, (int)trim($result[1]));
                    $data[$j++] = [
                        "place_uf" => $name,
                        "value" => trim($result[1]),
                        "id" => $ufs[trim($name)][0],
                        "displayValue" => $name,
                        "showLabel" => 1,
                        "simple_name" => strtolower($name)
                    ];
                }
            }
        }
        usort($data, function ($item1, $item2) {
            return $item2['value'] <=> $item1['value'];
        });
        $final_data = [
            'colors' => [
                [
                    "maxvalue" => count($all_values) > 0 ? max($all_values) : 0,
                    "code" => "#e44a00"
                ],
                [
                    "maxvalue" => count($all_values) > 0 ? max($all_values) / 2 : 0,
                    "code" => "#f8bd19"
                ]
            ],
            'data' => $data
        ];
        return $final_data;
    }

    public function getGrafico(string $selo, string $uf)
    {
        if ($uf == 'null') $uf = null;
        $name = "grafico" . $this->rename($selo) . "_" . $uf;
        $cache = Cache::get($name);
        $cache = explode("--", $cache);
        $data = ["goal" => $cache[0], "data" => [], "selo" => $selo];
        $i = 0;
        $this->gerenateArray($data, $cache, 1, "(Re)matrícula", $i);
        $this->gerenateArray($data, $cache, 2, "Cancelamento após (re)matrícula", $i);
        return $data;
    }

    public function getGraficoTenant(string $selo, string $tenantId)
    {
        $daily = DB::table("daily_metrics_consolidated")->select("date", DB::raw("in_school + in_observation as rematricula"), "justified_cancelled")->where("tenant_id", $tenantId  && $tenantId != "null" ? $tenantId : Auth::user()->isRestrictedToTenant())->get()->toArray();
        $data = ["goal" => "0", "data" => [], "selo" => $selo];
        $i = 0;
        $this->generateArrayTenant($daily, $data, $i, "(Re)matrícula", "rematricula");
        $this->generateArrayTenant($daily, $data, $i, "Cancelamento após (re)matrícula", "justified_cancelled");
        if (Auth::user()->isRestrictedToTenant()) {
            $data["goal"] = Auth::user()->tenant->city->goal ?
                $this->currentUser()->tenant->city->goal->goal + $this->currentUser()->tenant->city->goal->accumulated_ciclo1 : 0;
        } else {
            $goals = DB::table("tenants")
                ->join("cities", function ($join) {
                    $join->on("tenants.city_id", "=", "cities.id");
                })
                ->join("goals", function ($join) {
                    $join->on("goals.id", "=", "cities.ibge_city_id");
                })
                ->select(DB::raw("goal+accumulated_ciclo1  as goals"))
                ->where("tenants.id", "=", $tenantId)
                ->get()->toArray();
            if (count($goals) >= 1) $data["goal"] = $goals[0]->goals;
        }
        return response()->json(
            [
                'goal' => $data["goal"],
                'data' => $data["data"],
                'selo' => $selo
            ]
        );
    }

    private function gerenateArray(array &$data, array $cache, int $index, string $name, int &$curr_index)
    {
        for ($j = 1; $j < count($cache) - 2; $j += 3) {
            $data["data"][$curr_index++] = [
                "date" => $cache[$j],
                "value" => $cache[$j + $index],
                "tipo" => $name
            ];
        }
    }

    private function generateArrayTenant(array $dataRematricula, array &$data, int &$curr_index, string $name, string $index)
    {
        for ($i = 0; $i < count($dataRematricula); ++$i) {
            $data["data"][$curr_index++] = [
                "date" => $dataRematricula[$i]->date,
                "value" => $dataRematricula[$i]->$index,
                "tipo" => $name
            ];
        }
    }

    private function rename(string $name)
    {
        return strtolower($name[0]);
    }
}
