<?php


namespace BuscaAtivaEscolar\Mail;


use BuscaAtivaEscolar\StateSignup;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class StateManagerConfirmation extends Mailable
{

  protected $state_signup;

  public function __construct(StateSignup $state_signup)
  {
    $this->state_signup = $state_signup;
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
      ->subject("[Busca Ativa Escolar] Adesão estadual - Aviso importante!")
      ->line("Sr(a). Gestor(a) Estadual " . $this->state_signup->data["admin"]["name"])
      ->line("A Busca Ativa Escolar atualizou sua política de privacidade de acordo com a Lei Geral de Proteção de Dados (LGPD). Por isso, é necessário que o(a) gestor(a) politico(a) assine um novo termo de adesão contendo o compromisso de privacidade atualizado segundo a LGPD. O prazo para aceite é de 01 de outubro de 2021 até 30 de outubro de 2021. Depois disso, caso não seja aceito, a plataforma será bloqueada. Não deixe para a última hora!")
      ->action('Aceitar termo de adesão', $this->getUrlConfirmSignup())
      ->line('Atenciosamente, ')
      ->line('Equipe da Gestão Nacional da Busca Ativa Escolar');

    $this->subject("[Busca Ativa Escolar] Adesão municipal - Aviso importante!");

    $this->withSwiftMessage(function ($message) {
      $headers = $message->getHeaders();
      $headers->addTextHeader('message-id', $this->state_signup->id);
    });

    return $this->view('vendor.notifications.email', $message->toArray());
  }

  protected function getUrlConfirmSignup()
  {
    return env('APP_PANEL_URL') . "/confirmacao_prefeito/" . $this->state_signup->id;
  }
}
