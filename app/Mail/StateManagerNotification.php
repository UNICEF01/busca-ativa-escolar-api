<?php


namespace BuscaAtivaEscolar\Mail;


use BuscaAtivaEscolar\StateSignup;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class StateManagerNotification extends Mailable
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
      ->subject("[Busca Ativa Escolar] Adesão estadual")
      ->line("Sr(a). Gestor(a) Estadual " . $this->state_signup->data["admin"]["name"])
      ->line("Seu pedido de adesão/readesão à estratégia Busca Ativa Escolar foi aprovado. Por favor, confirme a adesão/readesão no botão abaixo.")
      ->action('Confirmar adesão/readesão', $this->getUrlConfirmSignup());

    $this->subject("[Busca Ativa Escolar] Adesão estadual");

    $this->withSwiftMessage(function ($message) {
      $headers = $message->getHeaders();
      $headers->addTextHeader('message-id', $this->state_signup->id);
    });

      return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());
  }

  protected function getUrlConfirmSignup()
  {
    return env('APP_PANEL_URL') . "/confirmacao_gestor_estadual/" . $this->state_signup->id;
  }
}
