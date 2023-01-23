<?php

namespace BuscaAtivaEscolar\Mail;

use BuscaAtivaEscolar\TenantSignup;
use BuscaAtivaEscolar\User;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class UserRegisterNotification extends Mailable
{
    const TYPE_REGISTER_INITIAL = "initial";
    const TYPE_REGISTER_REACTIVATION = "reactivation";

    protected $user;
    protected $type_register;

    public function __construct(User $user, $type_register){
        $this->user = $user;
        $this->type_register = $type_register;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "";

        if( $this->type_register == self::TYPE_REGISTER_INITIAL ) { $subject = "[Busca Ativa Escolar] Confirmação de cadastro"; }
        if( $this->type_register == self::TYPE_REGISTER_REACTIVATION ) { $subject = "[Busca Ativa Escolar] Confirmação de reativação"; }

        $message = (new MailMessage())
            ->success()
            ->subject($subject)
            ->line("Caro(a) usuário(a) ".$this->user->name)
            ->line("Você agora faz parte da equipe da Busca Ativa Escolar em seu município. Por favor, confirme seu cadastro clicando no botão abaixo.")
            ->action('Confirmar cadastro', $this->getUrlConfirmRegister());

        $this->subject($subject);

        /*
        $this->withSwiftMessage(function ($message) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('message-id', $this->user->id);
        });
        */

        return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());
    }

    protected function getUrlConfirmRegister(){
        return env('APP_PANEL_URL')."/user_setup/".$this->user->id."?token=".$this->user->getURLToken();
    }
}