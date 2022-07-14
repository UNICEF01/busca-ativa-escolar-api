<?php


namespace BuscaAtivaEscolar\Mail;


use BuscaAtivaEscolar\TenantSignup;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class MayorSignupConfirmation extends Mailable
{

  protected $tenant_signup;

  public function __construct(TenantSignup $tenant_signup)
  {
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
      ->subject("[Busca Ativa Escolar] Adesão municipal - Aviso importante!")
      ->line("Sr(a). Prefeito(a) " . $this->tenant_signup->data["mayor"]["name"])
      ->line("A Busca Ativa Escolar atualizou sua política de privacidade de acordo com a Lei Geral de Proteção de Dados (LGPD). Por isso, é necessário que o(a) prefeito(a) assine um novo termo de adesão contendo o compromisso de privacidade atualizado segundo a LGPD. O prazo para aceite é de 01 de outubro de 2021 até 30 de novembro de 2021. Depois disso, caso não seja aceito, a plataforma será bloqueada. Não deixe para a última hora!")
      ->action('Aceitar termo de adesão', $this->getUrlConfirmSignup());

    $this->subject("[Busca Ativa Escolar] Adesão municipal - Aviso importante!");

    $this->withSwiftMessage(function ($message) {
      $headers = $message->getHeaders();
      $headers->addTextHeader('message-id', $this->tenant_signup->id);
    });

    return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());
  }

  protected function getUrlConfirmSignup()
  {
    return env('APP_PANEL_URL') . "/confirmacao_prefeito/" . $this->tenant_signup->id;
  }
}
