<?php
/**
 * busca-ativa-escolar-api
 * TenantSignupRejected.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/02/2017, 15:57
 */

namespace BuscaAtivaEscolar\Mailables;


use BuscaAtivaEscolar\TenantSignup;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class TenantSignupRejected extends Mailable {

	public $signup;

	public function __construct(TenantSignup $signup) {
		$this->signup = $signup;
	}

	public function build() {

		$message = (new MailMessage())
			->subject("Sua adesão foi reprovada!")
			->greeting('Olá!')
			->line('Sua adesão ao programa Busca Ativa Escolar foi reprovada!')
			->line('Se desejar, entre em contato conosco através do e-mail abaixo:')
			->error()
			->action('contato@buscaativaescolar.org.br', 'mailto:contato@buscaativaescolar.org.br');

		$this->from(env('MAIL_USERNAME'), 'Busca Ativa Escolar');
		$this->subject("[Busca Ativa Escolar] Sua adesão foi reprovada!");

		return $this->view('vendor.notifications.email', $message->toArray());

	}

}