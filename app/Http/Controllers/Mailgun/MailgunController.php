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


class MailgunController extends BaseController
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {

        $message = $request->request;

        if ($message->has('signature') and $message->has('event-data')) {

            $signature = $message->get('signature');

            $timestamp = $signature['timestamp'];
            $token = $signature['token'];
            $signature = $signature['signature'];

            $event_data = $message->get('event-data');

            $this->validateokenMailgun($timestamp, $token, $signature);

            $message_id = $event_data['message']['headers']['mail_id'];
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
    public function validateokenMailgun($timestamp, $token, $signature)
    {
        if (hash_hmac("sha256", $timestamp . $token, env('MAILGUN_SECRET')) != $signature) {
            $data['status'] = "error";
            $data['message'] = "Invalid request";
            return response()->json($data, 403);
        }
    }
}
