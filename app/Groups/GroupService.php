<?php

namespace BuscaAtivaEscolar\Groups;

use DB;

class GroupService
{
    public function getGroup(string $name, $tenant): array
    {
        $data = DB::table('groups')->select('name as grupo', DB::raw("( select name from `groups` g where id = (select parent_id  from `groups` where name = '{$name}' and tenant_id = '{$tenant}')) as pai"), DB::raw("(select name from `groups` where id = (select parent_id from `groups` g where id = (select parent_id  from `groups` where name = '{$name}' and tenant_id = '{$tenant}'))) as avo"), DB::raw("(select name from `groups` where id = ( (select parent_id from `groups` where id = (select parent_id from `groups` g where id = (select parent_id  from `groups` where name = '{$name}' and tenant_id = '{$tenant}'))))) as bisavo"))->where([['name', $name], ['tenant_id', $tenant]])->get()->toArray();
        sort($data);
        return $data;
    }
}
