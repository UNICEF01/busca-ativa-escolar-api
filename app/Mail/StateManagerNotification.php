<?php

namespace BuscaAtivaEscolar\Mail;

use BuscaAtivaEscolar\StateSignup;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class StateManagerNotification extends Mailable
{

  protected $stateSignup;

  public function __construct(StateSignup $stateSignup)
  {
    $this->stateSignup = $stateSignup;
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
      ->line("Sr(a). Gestor(a) Estadual " . $this->stateSignup->data["admin"]["name"])
      ->line("O seu estado acaba de solicitar a adesão à Busca Ativa Escolar. Para concluir o processo clique no botão abaixo para ler e aceitar o Termo de Adesão e o compromisso de privacidade da plataforma.")
      ->action('Confirmar adesão', $this->getUrlConfirmSignup());

    $this->subject("[Busca Ativa Escolar] Adesão estadual");

    $this->withSwiftMessage(function ($message) {
      $headers = $message->getHeaders();
      $headers->addTextHeader('message-id', $this->stateSignup->id);
    });

    return $this->view('vendor.notifications.email', $message->toArray());
  }

  protected function getUrlConfirmSignup()
  {
    return env('APP_PANEL_URL') . "/confirmacao_gestor_estadual/" . $this->stateSignup->id;
  }
}
