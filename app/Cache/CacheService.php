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
}
