<?php

namespace BuscaAtivaEscolar\Groups;

use BuscaAtivaEscolar\Group;
use DB;

class GroupService
{

    /**
     * Método responsável por retornar o index do grupo do usuário no array de grupos. Utiliza busca linear.
     * @param array $arr Array com os ids, names e parent_ids dos grupos do município.
     * @param int $n  Tamanho do array $arr
     * @param string $target o id do grupo do usuário que será procurado.
     * 
     * @return int O índice do id do usuário no array $arr.
     */
    public function search(array $arr, int $n, string $target): int
    {
        if ($arr[$n - 1]['id'] == $target)
            return $n - 1;

        $backup = $arr[$n - 1]['id'];
        $arr[$n - 1]['id'] = $target;

        for ($i = 0;; $i++) {
            if ($arr[$i]['id'] == $target) {
                $arr[$n - 1]['id'] = $backup;
                if ($i < $n - 1)
                    return $i;
                return -1;
            }
        }
    }

    /**
     * Método responsável por retornar o index da primeira ocorrência de grupo  não filho do grupo do buscado no array de grupos. Utiliza busca binária pelo fato dos parent_id estarem ordenados.
     * @param array $data Array com os ids, names e parent_ids dos grupos do município.
     * @param string $target o id do grupo pai que será procurado.
     * 
     * @return int O índice do id do grupo não filho no array $data.
     */
    public function upper_bound(array $data, string $target): int
    {
        $low = 0;
        $high = count($data) - 1;
        while ($low <= $high + 1) {
            if ($low > $high)
                return $low;

            $mid = $low + (($high - $low) >> 1);

            if ($data[$mid]['parent_id'] > $target)
                $high = $mid - 1;
            else
                $low = $mid + 1;
        }
    }

    /**
     * Método responsável por retornar o index da primeira ocorrência de grupo filho do grupo do buscado no array de grupos. Utiliza busca binário pelo fato dos parent_id estarem ordenados.
     * @param array $data Array com os ids, names e parent_ids dos grupos do município.
     * @param string $target o id do grupo pai que será procurado.
     * 
     * @return int O índice do id do grupo filho no array $data.
     */
    public function lower_bound(array $data, string $target): int
    {
        $low = 0;
        $high = count($data) - 1;
        while ($low <= $high + 1) {
            if ($low > $high)
                return $low;

            $mid = $low + (($high - $low) >> 1);

            if ($data[$mid]['parent_id'] >= $target)
                $high = $mid - 1;
            else
                $low = $mid + 1;
        }
    }

    /**
     * Método responsável por retornar ou array de grupos com seus respectivos filhos até a quarta ordem hierárquica  ou array de ids dos grupos e seus filhos. É utilizado uma variável booleana de checagem.  
     * @param string $id String com o id do usuário
     * 
     * @return array O retorno será um array com os ids dos grupos.
     */
    public function groups(string $tenantId, string $groupId): array
    {

        $data = Group::select('id', 'name', 'parent_id')->where('tenant_id', $tenantId)->orderBy('parent_id')
            ->get()->toArray();

        $grupos = [];

        $size = count($data);
        $index = $this->search($data, $size, $groupId);
        $grupos[0] = ['id' => $data[$index]['id'], 'name' => $data[$index]['name']];

        $max = $this->upper_bound($data, $groupId);
        $min  = $this->lower_bound($data, $groupId);

        $groups_index = [];
        $indexs = 1;
        $groups_index[0] = $data[$index]['id'];

        $children = [];
        $children2 = [];

        $j = 0;
        $i = 0;
        while ($min < $max) {
            $groups_index[$indexs] = $data[$min]['id'];
            $max1 = $this->upper_bound($data, $data[$min]['id'], 0, $size - 1);
            $min1  = $this->lower_bound($data, $data[$min]['id'], 0, $size - 1);
            if ($min1 != $max1)
                $children[$j++] = ['pai' => $i, 'min' => $min1, 'max' => $max1];
            $i++;
            $indexs++;
            $min++;
        }

        $j = 0;
        foreach ($children as $child) {
            $i = 0;
            $min = $child['min'];
            $max = $child['max'];
            $pai = $child['pai'];
            while ($min < $max) {
                $groups_index[$indexs] = $data[$min]['id'];
                $max1 = $this->upper_bound($data, $data[$min]['id'], 0, $size - 1);
                $min1  = $this->lower_bound($data, $data[$min]['id'], 0, $size - 1);
                if ($min1 != $max1)
                    $children2[$j++] = ['pai' => $pai, 'pai1' => $i, 'min' => $min1, 'max' => $max1];
                $i++;
                $indexs++;
                $min++;
            }
        }
        $j = 0;
        foreach ($children2 as $child) {
            $i = 0;
            $min = $child['min'];
            $max = $child['max'];
            $pai = $child['pai'];
            while ($min < $max) {
                $groups_index[$indexs] = $data[$min]['id'];
                $max1 = $this->upper_bound($data, $data[$min]['id'], 0, $size - 1);
                $min1  = $this->lower_bound($data, $data[$min]['id'], 0, $size - 1);
                $i++;
                $indexs++;
                $min++;
            }
            $j++;
        }
        sort($groups_index);
        return $groups_index;
    }

    public function binarySearch(array $arr, string $target): bool
    {
        $l = 0;
        $r = count($arr);
        if ($r > 1) {
            while ($l <= $r) {
                $m = $l + (int)(($r - $l) / 2);

                $res = strcmp($target, $arr[$m]);

                if ($res == 0)
                    return true;
                if ($res > 0)
                    $l = $m + 1;
                else
                    $r = $m - 1;
            }
            return false;
        }
        return strcmp($target, $arr[0]) == 0;
    }

    public function getGroup(string $userGroupId, string $tenant): array
    {
        $data = DB::table('groups')->select('name as grupo', DB::raw("( select name from `groups` g where id = (select parent_id  from `groups` where id = '{$userGroupId}' and tenant_id = '{$tenant}')) as pai"), DB::raw("(select name from `groups` where id = (select parent_id from `groups` g where id = (select parent_id  from `groups` where id = '{$userGroupId}' and tenant_id = '{$tenant}'))) as avo"), DB::raw("(select name from `groups` where id = ( (select parent_id from `groups` where id = (select parent_id from `groups` g where id = (select parent_id  from `groups` where id = '{$userGroupId}' and tenant_id = '{$tenant}'))))) as bisavo"))->where([['id', $userGroupId], ['tenant_id', $tenant]])->get()->toArray();
        $dados = [];
        array_push($dados, $data[0]->grupo);
        if ($data[0]->pai)
            array_push($dados, $data[0]->pai);
        if ($data[0]->avo)
            array_push($dados, $data[0]->avo);
        if ($data[0]->bisavo)
            array_push($dados, $data[0]->bisavo);
        sort($dados);
        return $dados;
    }
}
