<?php

/**
 * Created by PhpStorm.
 * User: manoelfilho
 * Date: 2019-03-12
 * Time: 16:53
 */

namespace BuscaAtivaEscolar\Http\Controllers\Mailgun;

use BuscaAtivaEscolar\EmailJob;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Cast\Bool_;

class MailgunController extends BaseController
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {

        $requestData = $request->all();

        if (isset($requestData['signature']) && isset($requestData['event-data'])) {
            $signature = $requestData['signature'];
            $timestamp = $signature['timestamp'];
            $token = $signature['token'];
            $signature = $signature['signature'];

            $event_data = $requestData['event-data'];

            if ($this->validateTokenMailgun($timestamp, $token, $signature) == false) {
                $data['status'] = "error";
                $data['message'] = "Invalid request";
                return response()->json($data, 403);
            }

            // Inicializa $message_id como null
            // Em alguns casos subject nao existe e essa funcao retorna erro, ajustei para nao quebrar, mas temos que resolver esse problema. pq nao recebe o subject?
            $message_id = null; 
            if (isset($event_data['message']['headers']['subject'])) {
                $message_id = $this->getNumberOfNotification($event_data['message']['headers']['subject']);
            } else {
                $data['status'] = "success";
                return response()->json($data, 200);
            }

            $status_message = $event_data['event'];

            $emailJob = EmailJob::find($message_id);

            $data['status'] = "success";

            if ($emailJob != null) {
                $emailJob->status = $status_message;
                $emailJob->save();
                $data['message'] = "Email #" . $message_id . " updated!";
            }

            return response()->json($data, 200);
        } else {

            $data['status'] = "error";
            $data['message'] = "";
            return response()->json($data, 403);
        }
    }

    /**
     * @param $timestamp
     * @param $token
     * @param $signature
     * @return bool
     */
    public function validateTokenMailgun($timestamp, $token, $signature)
    {
        if (hash_hmac("sha256", $timestamp . $token, env('MAILGUN_SECRET_KEY_WEBHOOK')) != $signature) {
            return false;
        }
        return true;
    }

    public function getNumberOfNotification($subject)
    {
        preg_match('/#(\d+)/', $subject, $matches);
        if (isset($matches[1])) {
            return (int) $matches[1];
        } else {
            $data['status'] = "success";
            return response()->json($data, 200);
        }
    }
}
