<?php

namespace BuscaAtivaEscolar\Http\Controllers\Resources;

use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\NotificationCases\Interfaces\INotifications;
use Exception;
use Illuminate\Http\Request;

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

    public function update($id)
    {

        $result = ['status' => 200];

        try{
            $result['data'] = $this->notificationCaseService->resolveNotificationData($id);
            if(!$result['data'])
                return response()->json(['error' => 'Not allowed to solve this notification'], 403);
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
