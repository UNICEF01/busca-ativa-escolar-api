<?php


namespace BuscaAtivaEscolar\Mail;


use BuscaAtivaEscolar\TenantSignup;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class MayorSignupNotification extends Mailable
{

    protected $tenant_signup;

    public function __construct(TenantSignup $tenant_signup){
        $this->tenant_signup = $tenant_signup;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $message = (new MailMessage())
            ->success()
            ->subject("[Busca Ativa Escolar] Adesão municipal")
            ->line("Sr(a). Prefeito(a) ".$this->tenant_signup->data["mayor"]["name"])
            ->line("Seu pedido de adesão/readesão à estratégia Busca Ativa Escolar foi aprovado. Por favor, confirme a adesão/readesão no botão abaixo.")
            ->action('Confirmar adesão/readesão', $this->getUrlConfirmSignup());

        $this->subject("[Busca Ativa Escolar] Adesão municipal");

        $this->withSwiftMessage(function ($message) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('message-id', $this->tenant_signup->id);
        });

        return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());
    }

    protected function getUrlConfirmSignup(){
        return env('APP_PANEL_URL')."/confirmacao_prefeito/".$this->tenant_signup->id;
    }
}