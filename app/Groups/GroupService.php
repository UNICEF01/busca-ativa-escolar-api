<?php

namespace BuscaAtivaEscolar\Groups;

use DB;
use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\User;

class GroupService
{
    public function getGroup(string $name, $tenant): array
    {
        $data = DB::table('groups')->select('name as grupo', DB::raw("( select name from `groups` g where id = (select parent_id  from `groups` where name = '{$name}' and tenant_id = '{$tenant}')) as pai"), DB::raw("(select name from `groups` where id = (select parent_id from `groups` g where id = (select parent_id  from `groups` where name = '{$name}' and tenant_id = '{$tenant}'))) as avo"), DB::raw("(select name from `groups` where id = ( (select parent_id from `groups` where id = (select parent_id from `groups` g where id = (select parent_id  from `groups` where name = '{$name}' and tenant_id = '{$tenant}'))))) as bisavo"))->where([['name', $name], ['tenant_id', $tenant]])->get()->toArray();
        return $data;
    }

    public function binarySearch(array $arr, string $target): bool
    {
        $l = 0;
        $r = count($arr);
        while ($l <= $r) {
            $m = $l + (int)(($r - $l) / 2);

            $res = strcmp($target, $arr[$m]);

            if ($res == 0)
                return false;
            if ($res > 0)
                $l = $m + 1;
            else
                $r = $m - 1;
        }

        return true;
    }

    public function updateGroup(ChildCase $case, $attributes)
    {
        $case->group_id = $attributes['group_id'];
        $case->save();
        if ($attributes['detach_user'] == -1) {
            $case->currentStep->detachUser();
        }
        $case->child->save(); //reindex elastic*/
    }

    public function changeGroupx(array $data, string $tenant)
    {
        $grupos = $this->getGroup($data['group']['name'], $tenant);
        $dados = [];
        array_push($dados, $grupos[0]->grupo);
        array_push($dados, $grupos[0]->pai);
        array_push($dados, $grupos[0]->avo);
        array_push($dados, $grupos[0]->bisavo);
        sort($dados);
        for ($i = 0; $i < count($data['children']); ++$i) {
            if ($data['children'][$i]['assigned_user_id']) {
                $user = User::where('id', $data['children'][$i]['assigned_user_id'])->get()->toArray();
                if (!empty($user) && strpos($user[0]['type'], 'estadual') !== false)
                    $check = false;
                else {
                    if ($data['children'][$i]['assigned_group_name'])
                        $check = $this->binarySearch($dados, $data['children'][$i]['assigned_group_name']);
                    else
                        $check = false;
                }
            }
            $currentCase = [
                'id' => $data['children'][$i]['current_case_id'],
                'group_id' => $data['group']['id'],
                'detach_user' => $check ?? false
            ];
            $child = ChildCase::where('id', $data['children'][$i]['current_case_id'])->first();
            $this->updateGroup($child, $currentCase);
        }
    }
}
