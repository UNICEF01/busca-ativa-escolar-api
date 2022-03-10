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
     * Método responsável por retornar ou array de grupos com seus respectivos filhos até a quarta ordem hierárquica  ou array de ids dos grupos e seus filhos. É utilizado uma variável booleana de checagem.  
     * @param string $email String com o email do usuário
     * @param bool $check variavel de verificação
     * 
     * @return array O retorno será um array, porém depende do valor da variável check. Caso seja TRUE o retorno será uma array com os ids dos grupos, caso seja falso retornará o array de grupos com seus respectivos filhos. 
     */
    public function groups(string $email, bool $check): array{

        $userData = User::select('tenant_id', 'group_id')->where('email', $email)->get()->toArray();
        
        if(count($userData) == 0)
            return [];
            
        $data = Group::select('id','name', 'parent_id')->where('tenant_id', $userData[0]['tenant_id'])->orderBy('parent_id')
        ->get()->toArray();

        $grupos = [];

        $size = count($data);
        $index = $this->search($data, $size, $userData[0]['group_id']);
        $grupos[0] = ['id' => $data[$index]['id'], 'name' => $data[$index]['name']];
        
        $max = $this->upper_bound($data, $userData[0]['group_id']);
        $min  = $this->lower_bound($data, $userData[0]['group_id']);

        $groups_index = [];
        $indexs = 1;
        $groups_index[0] = $data[$index]['id'];

        $children = [];
        $children2 = [];

        $j = 0;
        $i = 0;
        while($min < $max){
            $grupos[0][$i] = ['id' => $data[$min]['id'], 'name' => $data[$min]['name']];
            $groups_index[$indexs] = $data[$min]['id'];
            $max1 = $this->upper_bound($data, $data[$min]['id'], 0, $size - 1);
            $min1  = $this->lower_bound($data, $data[$min]['id'], 0, $size - 1);
            if($min1 != $max1)
                $children[$j++] = ['pai' => $i, 'min' => $min1, 'max' => $max1];
            $i++;
            $indexs++;
            $min++;
        }

        $j = 0;
        foreach($children as $child){
            $i = 0;
            $min = $child['min'];
            $max = $child['max'];
            $pai = $child['pai'];
            while($min < $max){
                $grupos[0][$pai][$i] = ['id' => $data[$min]['id'], 'name' => $data[$min]['name']];
                $groups_index[$indexs] = $data[$min]['id'];
                $max1 = $this->upper_bound($data, $data[$min]['id'], 0, $size - 1);
                $min1  = $this->lower_bound($data, $data[$min]['id'], 0, $size - 1);
                if($min1 != $max1)
                    $children2[$j++] = ['pai' => $pai, 'pai1' => $i, 'min' => $min1, 'max' => $max1];
                $i++;
                $indexs++;
                $min++;   
            }
        }
        $j = 0;
        foreach($children2 as $child){
            $i = 0;
            $min = $child['min'];
            $max = $child['max'];
            $pai = $child['pai'];
            $pai1 = $child['pai1'];
            while($min < $max){
                $grupos[0][$pai][$pai1][$i] = ['id' => $data[$min]['id'], 'name' => $data[$min]['name']];
                $groups_index[$indexs] = $data[$min]['id'];
                $max1 = $this->upper_bound($data, $data[$min]['id'], 0, $size - 1);
                $min1  = $this->lower_bound($data, $data[$min]['id'], 0, $size - 1);
                $i++;
                $indexs++;
                $min++;
            }
            $j++;
        }
        if($check == false)
            return $grupos;
        return $groups_index;
    }
}