<?php

namespace BuscaAtivaEscolar\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Log;

class SchoolEducacensoNotification extends Mailable
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
            ->subject("[Busca Ativa Escolar] Escolas")
            ->line($this->school->name)
            ->line("Precisamos da sua colaboração!")
            ->line("Por meio da plataforma Busca Ativa Escolar, identificamos crianças e/ou adolescentes sendo que a última escola indicada na lista de matriculados e não localizados do Educacenso/INEP foi a sua. Necessitamos localizá-las e, para tanto, precisamos da sua colaboração para adicionar informações dos endereços das crianças e/ou adolescentes em destaque.")
            ->line("O procedimento é muito simples. Basta clicar no botão abaixo, acessar o cadastro de cada criança e/ou adolescente e complementar as informações requeridas.")
            ->line("Agradecemos imensamente sua disposição em colaborar para a garantia do direito à educação de todas as crianças e/ou adolescentes que residem em nosso município!")
            ->action('Colaborar', $this->getUrlToken());

        $message->withSwiftMessage(function ($message) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('X-Mailgun-Variables', json_encode([
                'mail_id' => $this->job_id
            ]));
        });

        $this->subject("Busca Ativa Escolar | Escolas");

        return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());
    }

    private function getUrlToken()
    {
        return env('APP_URL_ESCOLAS') . "?i=" . $this->school->id . "&t=" . $this->school->token;
    }
}
