<?php

namespace BuscaAtivaEscolar\Groups;

use BuscaAtivaEscolar\Group;
use Illuminate\Database\Eloquent\Builder;
use BuscaAtivaEscolar\ChildCase;

class GroupService
{

    public function changeAssignedUserCaseData(array $input)/*: array*/
    {
        $client = \Elasticsearch\ClientBuilder::create()->setHosts(['localhost:9200'])->build();
        $params = [
            'size' => 2000,
            'filter_path' => ['hits.hits._source.id', 'hits.hits._source.tree_id'],
            'pretty' => true,
            'index' => 'children',
            'type' => 'child',
            'body'  => [
                'query' => [
                    'term' => [
                        'assigned_user_id' => "{$input['user_id']}"
                    ]
                ]
            ]
        ];
        $user = $input['user_id'];
        $idsAndTreeIdsOfCases = array_map(function ($data) {
            return $data['_source'];
        }, $client->search($params)['hits']['hits']);

        $groups = implode(', ', Group::where('id', $input['group_id'])->get()->first()->getTree());
        $idsToRemoveUser = [];
        $updateRequestRemoveUserFromCase = ['conflicts' => 'proceed','index' => 'children', 'body' => ['query' => ['bool' => ['filter' => ['terms' => ['_id' => [],],],],], 'script' => ['inline' => "ctx._source.assigned_user_id = null; ctx._source.assigned_user_name = null; ctx._source.assigned_group_name = null"]]];
        $updateRequestMantainUserCase = ['conflicts' => 'proceed','index' => 'children', 'body' => ['query' => ['bool' => ['filter' => ['terms' => ['_id' => [],],],],], 'script' => ['inline' => "ctx._source.assigned_group_name = '{$input['group_name']}'; ctx._source.assigned_group_id = '{$input['group_id']}'"]]];
        foreach($idsAndTreeIdsOfCases as $data){
               if(strpos($data['tree_id'], $groups) !== false)
                 $updateRequestMantainUserCase['body']['query']['bool']['filter']['terms']['_id'][] = $data['id'];
               else{
                 $updateRequestRemoveUserFromCase['body']['query']['bool']['filter']['terms']['_id'][] = $data['id'];
                 array_push($idsToRemoveUser, $data['id']);
               }
        }
        sort($idsToRemoveUser);
        foreach (ChildCase::whereHas('currentStep', function (Builder $query) use ($user) {
            $query->where('assigned_user_id', '=', $user);
        })->get() as $case) {
            if($this->binarySearch($idsToRemoveUser, $case->child->id)){
                $case->currentStep->detachUser();
                $case->save();
            }
        }
        $client->updateByQuery($updateRequestRemoveUserFromCase);
        $client->updateByQuery($updateRequestMantainUserCase);
    }

    public function binarySearch(array $arr, string $target): bool
    {
        $l = 0;
        $r = count($arr) - 1;
        if ($r == 1)  return strcmp($target, $arr[0]) == 0;
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
}
