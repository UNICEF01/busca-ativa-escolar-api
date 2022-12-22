<?php
/**
 * busca-ativa-escolar-api
 * SupportTicket.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 14/07/2017, 15:58
 */

namespace BuscaAtivaEscolar\Mailables;


use BuscaAtivaEscolar\SupportTicket;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class NewSupportTicket extends Mailable {

	public $ticket;

	public function __construct(SupportTicket $ticket) {
		$this->ticket = $ticket;
	}

	public function build() {
		$message = (new MailMessage())
			->success()
			->subject("Nova solicitação de ajuda")
			->greeting("Solicitante: {$this->ticket->getName()}")
			->line("Município: {$this->ticket->getCityName()}")
			->line("E-mail: {$this->ticket->getEmail()}")
			->line("Telefone: {$this->ticket->getPhone()}")
			->line("Navegador: {$this->ticket->user_agent}");

		if($this->ticket->user) {
			$message->line("Tipo Usuário: " . trans('user.type.' . $this->ticket->user->type));
			$message->line("ID Usuário: {$this->ticket->user_id}");
		}

		$message->line("Mensagem: {$this->ticket->message}");

		$this->from(env('MAIL_USERNAME'), 'Busca Ativa Escolar');
		$this->subject("[Busca Ativa Escolar] Nova solicitação de suporte: {$this->ticket->getName()} ({$this->ticket->getCityName()})");

        return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());

	}

}