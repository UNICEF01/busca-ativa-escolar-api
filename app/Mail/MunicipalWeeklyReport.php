<?php

namespace BuscaAtivaEscolar\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class MunicipalWeeklyReport extends Mailable
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
            ->subject("Busca Ativa Escolar - Relatório Municipal")
            ->line("Prezado(a),")
            ->line("Segue o relatório municipal de hoje");
        $this->attach(storage_path("app/attachments/municipios.csv"));
        $this->subject("Busca Ativa Escolar - Relatório Municipal");

        return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());
    }
}