<?php

namespace BuscaAtivaEscolar\Http\Controllers\Resources;

use BuscaAtivaEscolar\Comment;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\NotificationCases\Interfaces\INotifications;
use Exception;
use Illuminate\Http\Request;
use Auth;

class NotificationCasesController extends BaseController
{
    protected $notificationCaseService;

    public function __construct(INotifications $notificationCaseService)
	{
		$this->notificationCaseService = $notificationCaseService;
	}

    public function store(Request $request)
    {
        $data = $request->only([
            'tenant_id',
            'user_id',
            'comment_id',
            'children_case_id',
            'notification',
        ]);

        $result = ['status' => 200];

        try{
            $result['data'] = $this->notificationCaseService->saveNotificationData($data);
        } catch(Exception $e){
            $result = [
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }
        
        return response()->json($result, $result['status']);
    }

    public function update(Request $request)
    {
        $data = $request->only([
            'id',
            'annotation',
        ]);

        $result = ['status' => 200];

        try{
            $result['data'] = $this->notificationCaseService->resolveNotificationData($data['id']);

            if($result['data']){
                $notification = $this->notificationCaseService->findNotificationData($data['id']);
                Comment::post($notification->case->child, Auth::user(), $data['annotation']);
            } else {
                return response()->json(['error' => 'Not allowed to solve this notification'], 403);
            }

        } catch(Exception $e){
            $result = [
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }

        return response()->json($result, $result['status']);

    }

    public function getList()
    {
        
        $result = ['status' => 200];

        try{
            $result['data'] = $this->notificationCaseService->findAllNotificationDataByUser();
        } catch(Exception $e){
            $result = [
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }
        
        return response()->json($result, $result['status']);
    }

}
