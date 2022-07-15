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
use BuscaAtivaEscolar\User;
use Carbon\Carbon;

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
            'comment_id' => 'required',
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
        $checkUserPermission = $this->noticationsCaseRepository->find($id);
        if(\Auth::user()->tree_id != $checkUserPermission->users_tree_id)
            return false;
            
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
        $tree = explode(", ",$group[0]->tree_id);
        if(count($tree) == 2 || count($tree) == 1) return $tree[0];
        $data = User::select('tree_id')->where('group_id', $tree[2])->where('type', 'coordenador_operacional')->orWhere('type', 'supervisor_institucional')->distinct()->get()->toArray();
        return $data[0]['tree_id'];
    }

    public function findAllNotificationDataByUser(string $treeId): ?object
    {
        $notificationData = $this->noticationsCaseRepository->findAll($treeId);
        $result = [];
        $i = 0;
        foreach ($notificationData as $notification) {
            $user = User::select('id', 'name')->where('id', $notification->user_id)->get()->toArray();
            $link = ChildCase::select('child_id')->where('id', $notification->children_case_id)->get()->toArray();
            $result[$i]['id'] = $notification->id;
            $result[$i]['user_id'] = $user[0]['id']; 
            $result[$i]['user_name'] = $user[0]['name']; 
            $result[$i]['link'] = $link[0]['child_id']; 
            $result[$i]['create_date'] = $notification->created_at->format('d/m/Y');
            $i++;
        }
        return (object) $result;
    }

    public function checkComment(string $id)
    {
        return $this->noticationsCaseRepository->getComment($id);
    }
}
