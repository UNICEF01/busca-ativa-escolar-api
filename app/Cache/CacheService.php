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
    
    private function getAlert(string $region_or_uf): array
    {
        $region_or_uf = $region_or_uf == "BR" ? "" : $region_or_uf;
        $alerts = explode(" ", Cache::get("alerts_" . $region_or_uf));
        return [
            "total" => $alerts[0] + $alerts[1] + $alerts[2],
            "approved" => $alerts[0],
            "pending" => $alerts[1],
            "rejected" => $alerts[2]
        ];
    }

    private function getUfs(string $region_or_uf): array
    {
        $region_or_uf = $region_or_uf == "BR" ? "" : $region_or_uf;
        $ufs = Cache::get("state_signup_" . $region_or_uf);
        return [
            "is_approved" => $ufs,
            "num_ufs" => $ufs,
            "num_pending_state_signups" => 0,
        ];
    }

    private function getTenantSignup(string $region_or_uf): array
    {
        $region_or_uf = $region_or_uf == "BR" ? "" : $region_or_uf;
        $tenants_signup = explode(" ", Cache::get("tenant_signup_" . $region_or_uf));
        return [
            "num_pending_setup" => $tenants_signup[1],
            "num_pending_signups" => $tenants_signup[2]
        ];
    }

    private function getTenantsStatus(string $region_or_uf): array{
        $region_or_uf = $region_or_uf == "BR" ? "" : $region_or_uf;
        $tenants = explode(" ", Cache::get("tenants_" . $region_or_uf));
        return [
            "num_tenants" => $tenants[0],
            "active" => $tenants[1],
            "inactive" => $tenants[2],
        ];
    }

    private function getTenants(string $region_or_uf): array{
        $signups = $this->getTenantSignup($region_or_uf);
        $tenants = $this->getTenantsStatus($region_or_uf);
        return [
            "num_tenants" => $tenants["num_tenants"],
            "active" => $tenants["active"],
            "inactive" => $tenants["inactive"],
            "num_signups" => $tenants["num_tenants"] + $signups["num_pending_setup"],
            "num_pending_setup" => $signups["num_pending_setup"],
            "num_pending_signups" => $signups["num_pending_signups"]
        ];
    }

    private function getCases(string $region_or_uf): array{
        $region_or_uf = $region_or_uf == "BR" ? "" : $region_or_uf;
        $causes = explode(" ", Cache::get("cases_" . $region_or_uf));
        return [
            "total" => $causes[2] + $causes[3] + $causes[4] + $causes[5] + $causes[6] + $causes[7],
            "in_progress" => $causes[0],
            "enrollment" => $causes[1],
            "in_school" => $causes[2],
            "in_observation" => $causes[3],
            "out_of_school" => $causes[4],
            "cancelled" => $causes[5],
            "transferred" => $causes[6],
            "interrupted" => $causes[7],
        ];
    }

    private function getReasonsCauses(string $region_or_uf): array {
        $region_or_uf = $region_or_uf == "BR" ? "" : $region_or_uf;
        $reason_causes = [];
        $reasons = explode(" ", Cache::get("reason_cases_" . $region_or_uf));
        $i = 0;
        foreach (CaseCause::getAll() as $case) 
            array_push($reason_causes, ['id' => $case->id, 'cause' => $case->label, 'qtd' => $reasons[$i++]]);
        return $reason_causes;
    }

    private function getTenantsIds(string $region_or_uf){
        $reg = ["N", "NE", "CO", "SD", "S", "BR"];
        if (!in_array($region_or_uf, $reg))
            return Tenant::getIDsWithinUF($region_or_uf);
        return "";
    }

    private function getCityIds(string $region_or_uf){
        $reg = ["N", "NE", "CO", "SD", "S", "BR"];
        if (!in_array($region_or_uf, $reg))
            return  City::getIDsWithinUF($region_or_uf);
    }

    public function returnData(string $region_or_uf): array
    {
        $data = [];
        $data  = [
            "ufs" => $this->getUfs($region_or_uf),
            "tenants" => $this->getTenants($region_or_uf),
            "alerts" => $this->getAlert($region_or_uf),
            "cases" => $this->getCases($region_or_uf),
            'causes_cases' => $this->getReasonsCauses($region_or_uf),
            'tenant_ids' => $this->getTenantsIds($region_or_uf),
            'city_ids' => $this->getCityIds($region_or_uf)
        ];
        return $data;
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
