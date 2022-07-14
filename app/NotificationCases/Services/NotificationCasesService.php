<?php

namespace BuscaAtivaEscolar\NotificationCases\Services;

use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\NotificationCases\Interfaces\INotifications;
use BuscaAtivaEscolar\NotificationCases\Repositories\NotificationCasesRepository;
use InvalidArgumentException;
use Validator;
use DB;
use Exception;
use Log;

class NotificationCasesService implements INotifications
{
    protected $noticationsCaseRepository;

    public function __construct(NotificationCasesRepository $noticationsCaseRepository)
    {
        $this->noticationsCaseRepository = $noticationsCaseRepository;
    }

    public function saveNotificationData(array $attributes): object
    {
        
        $validator = Validator::make($attributes,[
            'tenant_id' => 'required',
            'user_id' => 'required',
            'children_case_id' => 'required',
            'notification' => 'required',
        ]);
        
        if($validator->fails()){
            throw new InvalidArgumentException($validator->errors()->first());
        }
        $attributes['case_tree_id'] = ChildCase::select('tree_id')->where('id', $attributes['children_case_id'])->get()->toArray()[0]['tree_id'];;
        $attributes['users_tree_id'] = $this->getTrees($attributes['children_case_id']);
        $result = $this->noticationsCaseRepository->save($attributes);
        return $result;
    }

    public function findNotificationData(string $id): ?object
    {
        return $this->noticationsCaseRepository->find($id);
    }

    public function deleteNotificationData(string $id): bool
    {
        DB::beginTransaction();

        try{
            $notification = $this->noticationsCaseRepository->delete($id);
        } catch(\Exception $e){
            DB::rollBack();
            \Log::info($e->getMessage());
            
            throw new InvalidArgumentException('Unable to delete notification case data');
        }
        
        DB::commit();

        return $notification;
    }

    public function resolveNotificationData(string $id): bool
    {
        DB::beginTransaction();

        try{
            $notification = $this->noticationsCaseRepository->update([],$id);
        }catch(Exception $e){
            DB::rollBack();
            Log::info($e->getMessage());

            throw new InvalidArgumentException('Unable to solve notificaiton case');
        }
        DB::commit();

        return $notification;
    }

    public function getTrees(string $id): string
    {  
        $group = ChildCase::select('tree_id')->where('id', $id)->get();
        $tree = explode(",",$group[0]->tree_id);
        $treeId = (count($tree) == 2 || count($tree) == 1 ? ltrim($tree[0]) : $group[0]->tree_id == 3) ? ltrim($tree[1]) : ltrim($tree[2]);
        $data = DB::table('users')
        ->select('tree_id',DB::raw('count(distinct tree_id) - count(distinct case when `type` = \'coordenador_operacional\' or `type` = \'supervisor_institucional\' then tree_id end) as total'))
        ->where('tree_id','LIKE','%'.$treeId.'%')
        ->groupBy('tree_id')
        ->havingRaw('total = ?',[0])
        ->orderByRaw('LENGTH(tree_id) DESC')
        ->limit(1)
        ->get()->toArray();
        return $data[0]->tree_id;
    }

    public function findAllNotificationDataByUser(string $treeId): ?object
    {
        return $this->noticationsCaseRepository->findAll($treeId);
    }
}