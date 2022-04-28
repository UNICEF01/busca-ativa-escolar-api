<?php

namespace BuscaAtivaEscolar\Cache;

use Cache;
use BuscaAtivaEscolar\Data\CaseCause;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\City;

class CacheService
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

        $cache = Cache::get("report_cache");
        $data = explode("\n", $cache);
        $states = explode(" ", $data[0]);
        $tenants = explode(" ", $data[1]);
        $tenantsSignup = explode(" ", $data[2]);
        $alerts = explode(" ", $data[3]);
        $cases = explode(" ", $data[4]);
        $rcases = explode(" ", $data[5]);

        $stateIndex = $this->search($states, count($states), $searched);
        $tenantsIndex = $this->search($tenants, count($tenants), $searched);
        $tenantsSignupIndex = $this->search($tenantsSignup, count($tenantsSignup), $searched);
        $alertsIndex = $this->search($alerts, count($alerts), $searched);
        $casesIndex = $this->search($cases, count($cases), $searched);
        $rcasesIndex = $this->search($rcases, count($rcases), $searched);
        $rcasesIndex += 1;
        $causes = [];
        if (!in_array($searched, $reg)) {
            $tenantIDs = Tenant::getIDsWithinUF($searched);
            $cityIDs = City::getIDsWithinUF($searched);
        }
        foreach (CaseCause::getAll() as $case) {

            array_push($causes, ['id' => $case->id, 'cause' => $case->label, 'qtd' => $rcases[$rcasesIndex++]]);
        }
        $data = [
            'ufs' => [
                'is_approved' => $states[$stateIndex + 1],
                'num_ufs' => $states[$stateIndex + 1],
                'num_pending_state_signups' =>  in_array($searched, $reg) ? $states[$stateIndex + 2] : 0,
            ],
            'tenants' => [
                'num_tenants' => $tenants[$tenantsIndex + 1],
                'active' => $tenants[$tenantsIndex + 2],
                'inactive' => $tenants[$tenantsIndex + 3],
                'num_signups' => $tenantsSignup[$tenantsSignupIndex + 1],
                'num_pending_setup' => $tenantsSignup[$tenantsSignupIndex + 2],
                'num_pending_signups' => $tenantsSignup[$tenantsSignupIndex + 3],
            ],
            'alerts' => [
                '_total' => intval($alerts[$alertsIndex + 1]) + intval($alerts[$alertsIndex + 2]) + intval($alerts[$alertsIndex + 3]),
                '_approved' => $alerts[$alertsIndex + 1],
                '_pending' => $alerts[$alertsIndex + 2],
                '_rejected' => $alerts[$alertsIndex + 3],
            ],
            'cases' => [
                '_total' => $cases[$casesIndex + 3] + $cases[$casesIndex + 4] + $cases[$casesIndex + 5] + $cases[$casesIndex + 6] + $cases[$casesIndex + 7] + $cases[$casesIndex + 8],
                '_in_progress' => $cases[$casesIndex + 1],
                '_enrollment' => $cases[$casesIndex + 2],
                '_in_school' => $cases[$casesIndex + 3],
                '_in_observation' => $cases[$casesIndex + 4],
                '_out_of_school' => $cases[$casesIndex + 5],
                '_cancelled' => $cases[$casesIndex + 6],
                '_transferred' => $cases[$casesIndex + 7],
                '_interrupted' => $cases[$casesIndex + 8],
            ],
            'causes_cases' => $causes,
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
            $string = $arr[$m][0] == '&' ? substr($arr[$m], 1, 2) : substr($arr[$m], 0, 2);
            $res = strcmp($target, $string);

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
        $cache = Cache::get("map_cache");
        $cache = explode("-BR", $cache);
        $states = explode(" uf ", $cache[0]);
        $country = explode(" ", $cache[1]);
        $country = explode("&", $country[0]);
        $k = 0;
        $all_values = [];
        if ($uf != null and $uf != "null") {
            $index =  $this->binarySerchString($states, $uf);
            $state = explode("city", $states[$index]);
            for ($i = 0; $i < count($state) - 1; ++$i) {
                array_push($all_values, explode("&", $state[$i])[4]);
                $data[$k++] = [
                    "id" => explode("&", $state[$i])[2],
                    "value" => explode("&", $state[$i])[4],
                    "name_city" => utf8_encode(explode("&", $state[$i])[3]),
                    "showLabel" => 0,
                ];
            }
        } else {
            $k = 0;
            $j = 2;
            for ($i = 1; $i < count($country) - 2 && $j < count($country) - 1; $i += 2, $j += 2) {
                array_push($all_values, $country[$j]);
                $name = $country[$i];
                if ($country[$j] > 0) {
                    $data[$k++] = [
                        "place_uf" => $name,
                        "value" => $country[$j],
                        "id" => $ufs[$name][0],
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
}
