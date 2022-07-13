<?php

namespace BuscaAtivaEscolar\EmailTypes;

use BuscaAtivaEscolar\Mail\SchoolFrequencyNotification;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class ClassFrequencyNotification extends Mailable
{

    protected $periodicidade;
    protected $school;

    public function __construct($school, $periodicidade)
    {
        $this->school = $school;
        $this->periodicidade = $periodicidade;
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

            ->subject("[Busca Ativa Escolar] Controle de frequência")

            ->line($this->school->name. " - INEP: " .$this->school->id)

            ->line("O Comitê Gestor da Busca Ativa Escolar de seu município solicita o registro de acompanhamento de frequência das turmas cadastradas por sua escola.")

            ->action('Cadastrar frequências', $this->getUrlToken());

        $this->subject("[Busca Ativa Escolar] Controle de frequência");

        return $this->view('vendor.notifications.email', $message->toArray());
    }

    private function getUrlToken()
    {
        return env('APP_PANEL_URL') . "/frequencia/" . $this->school->id;
    }
}