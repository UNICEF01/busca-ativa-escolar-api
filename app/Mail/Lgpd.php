<?php


namespace BuscaAtivaEscolar\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class Lgpd extends Mailable
{

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    $message = (new MailMessage())
      ->success()
      ->subject("Busca Ativa Escolar - Relatório LGPD")
      ->line("Prezado(a),")
      ->line("Este é o relatório do aceite do termo dos municípios, estados e dos usuários exportado hoje às 9h.");
    $this->attach(storage_path("app/attachments/relatorio_estadual.csv"));
    $this->attach(storage_path("app/attachments/relatorio_municipal.csv"));
    $this->attach(storage_path("app/attachments/relatorio_usuario.csv"));
    $this->subject("Busca Ativa Escolar - Relatório LGPD");

    /*
    $this->withSwiftMessage(function ($message) {
      $headers = $message->getHeaders();
      $headers->addTextHeader('message-id', '123456');
    });
    */

    
return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());
  }
}