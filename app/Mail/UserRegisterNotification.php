<?php

namespace BuscaAtivaEscolar\Mail;

use BuscaAtivaEscolar\User;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class UserRegisterNotification extends Mailable
{
    const TYPE_REGISTER_INITIAL = "initial";
    const TYPE_REGISTER_REACTIVATION = "reactivation";

    protected $user;
    protected $type_register;
    protected $type_tenant;

    public function __construct(User $user, $type_register)
    {
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
        $subject = $this->type_register == self::TYPE_REGISTER_INITIAL ?
            "[Busca Ativa Escolar] Confirmação de cadastro" :
            "[Busca Ativa Escolar] Confirmação de reativação";

        if ($this->user->tenant()) {
            $this->type_tenant = $this->user->tenant->is_state ? "estado" : "município";
        }

        $message = (new MailMessage())
            ->success()
            ->subject($subject)
            ->line("Caro(a) usuário(a) " . $this->user->name)
            ->line("Você agora faz parte da equipe da Busca Ativa Escolar em seu " . $this->type_tenant . " Por favor, confirme seu cadastro clicando no botão abaixo.")
            ->action('Confirmar cadastro', $this->getUrlConfirmRegister());

        $this->subject($subject);

        return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());
    }

    protected function getUrlConfirmRegister()
    {
        return env('APP_PANEL_URL') . "/user_setup/" . $this->user->id . "?token=" . $this->user->getURLToken();
    }
}
