<?php
/**
 * busca-ativa-escolar-api
 * TenantSignupApproved.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/02/2017, 15:57
 */

namespace BuscaAtivaEscolar\Mailables;

use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\TenantSignup;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class TenantSignupApproved extends Mailable {

	public $signup;

	public function __construct(TenantSignup $signup) {
		$this->signup = $signup;
	}

	public function build() {

		$setupToken = $this->signup->getURLToken();
		$setupURL = env('APP_PANEL_URL') . "/admin_setup/" . $this->signup->id . '?token=' . $setupToken;

		$message = (new MailMessage())
			->subject("Sua adesão foi aprovada!")
			->greeting('Olá!')
			->line("A adesão/readesão de {$this->signup->city->name} à Busca Ativa Escolar foi aprovada.")
			->line('Clique no botão abaixo para configurar a plataforma.')
			->success()
			->action('Configurar', $setupURL);

		$this->from(env('MAIL_USERNAME'), 'Busca Ativa Escolar');
		$this->subject("[Busca Ativa Escolar] Sua adesão foi aprovada!");

        return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());

	}

}