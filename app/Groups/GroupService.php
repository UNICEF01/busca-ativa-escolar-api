<?php

namespace BuscaAtivaEscolar\Groups;
use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\User;

class GroupService{

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
  
        for ($i = 0; ; $i++)
        {
            if ($arr[$i]['id'] == $target) 
            { 
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
        while($low <= $high + 1){
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
        while($low <= $high + 1){    
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
     * Método responsável por retornar o array de grupos com seus respectivos filhos até a quarta ordem hierárquica.  
     * @param string $email String com o email do usuário
     * 
     * @return array Array de grupos. 
     */
    public function groups(string $email): array{

        $userData = User::select('tenant_id', 'group_id')->where('email', $email)->get()->toArray();
        
        if(count($userData) == 0)
            return [];
            
        $data = Group::select('id','name', 'parent_id')->where('tenant_id', $userData[0]['tenant_id'])->orderBy('parent_id')
        ->get()->toArray();

        $grupos = [];
        $index = $this->search($data, count($data), $userData[0]['group_id']);
        $grupos[0] = ['id' => $data[$index]['id'], 'name' => $data[$index]['name']];

        $max = $this->upper_bound($data, $userData[0]['group_id']);
        $min  = $this->lower_bound($data, $userData[0]['group_id']);

        $children = [];
        $children2 = [];
        $i = 0;

        while($min < $max){
            $grupos[0][$i] = ['id' => $data[$min]['id'], 'name' => $data[$min]['name']];
            $max1 = $this->upper_bound($data, $data[$min]['id']);
            $min1  = $this->lower_bound($data, $data[$min]['id']);
            if($min1 != $max1)
                $children[$i] = ['pai' => $i, 'min' => $min1, 'max' => $max1];
            $i++;
            $min++;
        }

        for($j = 0; $j < count($children); ++$j){
            $i = 0;
            $min = $children[$j]['min'];
            $max = $children[$j]['max'];
            while($min < $max){
                $grupos[0][$children[$j]['pai']][$i] = ['id' => $data[$min]['id'], 'name' => $data[$min]['name']];
                $max1 = $this->upper_bound($data, $data[$min]['id']);
                $min1  = $this->lower_bound($data, $data[$min]['id']);
                if($min1 != $max1)
                    $children2[$i] = ['pai' => $j, 'pai1' => $i, 'min' => $min1, 'max' => $max1];
                $i++;
                $min++;
            }
        }

        for($j = 0; $j < count($children2); ++$j){
            $i = 0;
            $min = $children2[$j]['min'];
            $max = $children2[$j]['max'];
            while($min < $max){
                $grupos[0][$children2[$j]['pai']][$children2[$j]['pai1']][$i] = ['id' => $data[$min]['id'], 'name' => $data[$min]['name']];
                $max1 = $this->upper_bound($data, $data[$min]['id']);
                $min1  = $this->lower_bound($data, $data[$min]['id']);
                $i++;
                $min++;
            }  
        }
        return $grupos;
    }
}