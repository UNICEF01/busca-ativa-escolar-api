<?php

namespace BuscaAtivaEscolar\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class SchoolFrequencyNotification extends Mailable
{

    protected $job_id;
    protected $school;

    public function __construct($school, $job_id)
    {
        $this->job_id = $job_id;
        $this->school = $school;
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

            ->line("Precisamos da sua colaboração!")

            ->line("O Comitê Gestor da Busca Ativa Escolar do seu município solicita o cadastro das turmas da sua escola para o acompanhamento da frequência dos estudantes.")

            ->action('Cadastrar turmas', $this->getUrlToken());

        $this->subject("[Busca Ativa Escolar] Controle de frequência");

        $this->withSwiftMessage(function ($message) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('message-id', $this->job_id);
        });

        return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());
    }

    private function getUrlToken()
    {
        return env('APP_PANEL_URL') . "/turmas/" . $this->school->id;
    }
}
