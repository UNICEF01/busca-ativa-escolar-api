<?php
/**
 * busca-ativa-escolar-api
 * SignupApproved.php
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
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\User;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class UserRegistered extends Mailable {

	public $tenant;
	public $user;
	public $initialPassword;

	public function __construct(Tenant $tenant, User $user, $initialPassword) {
		$this->tenant = $tenant;
		$this->user = $user;
		$this->initialPassword = $initialPassword;
	}

	public function build() {

		$loginURL = env('APP_PANEL_URL') . "/login";

		$message = (new MailMessage())
			->subject("Novo usuário em {$this->tenant->name}")
			->greeting("Olá, {$this->user->name}")
			->line("Seu perfil de acesso à plataforma Busca Ativa Escolar foi criado.")
			->line("Dados de acesso:")
			->line("**Usuário:** --{$this->user->email}--")
			//->line("**Senha temporária:** --{$this->initialPassword}--")
			->line("**Perfil:** --" . trans('user.type.' . $this->user->type) . '--')
			->line("Caso queira alterar sua senha e personalizar seu perfil, clique na seta ao lado do seu nome, no menu, e depois vá em 'Preferências'.")
			->success()
			->action('Acessar agora', $loginURL);

		$this->from(env('MAIL_USERNAME'), 'Busca Ativa Escolar');
		$this->subject("[Busca Ativa Escolar] Novo usuário em {$this->tenant->name}");

        return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());

	}

}