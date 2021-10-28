<?php

namespace BuscaAtivaEscolar\Http\Controllers;

use BuscaAtivaEscolar\Mail\MayorSignupConfirmation;
use BuscaAtivaEscolar\TenantSignup;
use Illuminate\Support\Facades\Mail;

class SendMail extends BaseController
{
    //
    public function sendMailTenant()
    {
        $tenant = TenantSignup::select('id', 'data')->get()->toArray();
        array_walk($tenant, function (&$value, $key) {
            unset(
                $value['data']['admin'],
                $value['data']['city_id'],
                $value['data']['mayor']['cpf'],
                $value['data']['mayor']['dob'],
                $value['data']['mayor']['phone'],
                $value['data']['mayor']['mobile'],
                $value['data']['mayor']['titulo'],
            );
            $value['mail'] = strtolower($value['data']['mayor']['email']);
            $value['name'] = $value['data']['mayor']['name'];
            unset($value['data']);
        });


        for ($i = 0; $i < count($tenant); $i++) {
            $tenantData = new TenantSignup();
            $tenantData = $tenantData->where('id', $tenant[$i]['id'])->first();
            $message = new MayorSignupConfirmation($tenantData);
            Mail::to($tenantData->data['mayor']['email'])->send($message);
        }
    }
}
