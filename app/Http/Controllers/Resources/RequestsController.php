<?php

namespace BuscaAtivaEscolar\Http\Controllers\Resources;

use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Mail\ReopenCaseNotification;
use BuscaAtivaEscolar\ReopeningRequests;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\User;
use Illuminate\Support\Facades\Mail;

class RequestsController extends BaseController
{

    public function all()
    {

        /* @var $tenant Tenant */
        $tenant = \Auth::user()->tenant;

        /* @var $user User */
        $user = \Auth::user();

        $userType = $user->type;

        $user->type = User::TYPE_GESTOR_NACIONAL;
        
        if ($userType == 'supervisor_institucional') {
            $column = 'requester_id';
            $param = $user->id;
        } else {
            $column = 'tenant_recipient_id';
            $param = $tenant->id;
        }

        $requests = ReopeningRequests::query()
            ->with(
                [
                    'child',
                    'requester',
                    'recipient'
                ]
            )
            ->where($column, $param)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $requests]);
    }

    public function reject(ReopeningRequests $request)
    {

        $rejectReason = request('reject_reason');

        if ($request->status == ReopeningRequests::STATUS_CANCELLED) {
            return response()->json(['status' => 'error', 'result' => 'Solicitação já rejeitada']);
        }

        if ($request->status == ReopeningRequests::STATUS_APPROVED) {
            return response()->json(['status' => 'error', 'result' => 'Solicitação já aprovada']);
        }

        /* @var $user User */
        $user = \Auth::user();

        /* */
        $user->type = User::TYPE_GESTOR_NACIONAL;

        if ($request == null) {
            return response()->json(['status' => 'error', 'result' => 'Solicitação não localizada']);
        }
        if ($rejectReason == null) {
            return response()->json(['status' => 'error', 'result' => 'Por favor informe o motivo da rejeição!']);
        }

        $request->reject_reason = $rejectReason;

        $request->status = ReopeningRequests::STATUS_CANCELLED;

        if ($request->type_request == ReopeningRequests::TYPE_REQUEST_TRANSFER) {
            $type_answer = ReopenCaseNotification::TYPE_REJECT_TRANSFER;
        }

        if ($request->type_request == ReopeningRequests::TYPE_REQUEST_REOPEN) {
            $type_answer = ReopenCaseNotification::TYPE_REJECT_REOPEN;
        }

        $request->save();

        if ($request->requester != null) {

            try {

                $msg = new ReopenCaseNotification(
                    $request->child->id,
                    $request->child->name,
                    $request->child->current_case_id,
                    null,
                    $request->requester->name,
                    \Auth::user()->name,
                    $request->id,
                    $request->tenantRequester,
                    $request->tenantRecipient,
                    $type_answer,
                    $rejectReason
                );

                Mail::to($request->requester->email)->send($msg);
            } catch (\Exception $e) {
                //TODO Erro de envio de email
            }
        }

        /* */
        $user->type = User::TYPE_GESTOR_OPERACIONAL;

        return response()->json(['status' => 'success', 'result' => 'Solicitação rejeitada com sucesso']);
    }
}
