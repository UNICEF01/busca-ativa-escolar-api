<?php

namespace BuscaAtivaEscolar\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class Seal extends Mailable
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
        ->subject("Busca Ativa Escolar - Relatório Selo")
        ->line("Prezado(a),")
        ->line("Este é o relatório do SELO exportado hoje às 12h.");
      $this->attach(storage_path("app/attachments/seal.csv"));
      $this->subject("Busca Ativa Escolar - Relatório Selo");
  
      $this->withSwiftMessage(function ($message) {
        $headers = $message->getHeaders();
        $headers->addTextHeader('message-id', '123456');
      });
  
      return $this->view('vendor.notifications.email', $message->toArray());
    }
  }
