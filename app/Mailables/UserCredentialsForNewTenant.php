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

class UserCredentialsForNewTenant extends Mailable {

	public $signup;
	public $tenant;
	public $user;
	public $initialPassword;

	public function __construct(TenantSignup $signup, Tenant $tenant, User $user, $initialPassword) {
		$this->signup = $signup;
		$this->tenant = $tenant;
		$this->user = $user;
		$this->initialPassword = $initialPassword;
	}

	public function build() {

		$loginURL = env('APP_PANEL_URL') . "/login";

		$message = (new MailMessage())
			->subject("A plataforma de {$this->tenant->name} está pronta para acesso!")
			->greeting("Olá, {$this->user->name}")
			->line("Seu cadastro na Busca Ativa Escolar foi concluído com sucesso e a plataforma está pronta para uso.")
			->line("Seus dados para acesso são:")
			->line("**Usuário:** --{$this->user->email}--")
			//->line("**Senha temporária:** --{$this->initialPassword}--")
			->line("**Perfil:** --" . trans('user.type.' . $this->user->type) . '--')
			->line("Caso queira alterar sua senha e personalizar seu perfil na plataforma, clique na seta ao lado do seu nome, no menu, e depois vá em 'Preferências'.")
			->line('Clique no botão abaixo para entrar na plataforma.')
			->success()
			->action('Acessar', $loginURL);

		$this->from(env('MAIL_USERNAME'), 'Busca Ativa Escolar');
		$this->subject("[Busca Ativa Escolar] A plataforma de {$this->tenant->name} está pronta para acesso!");

        return $this->view(['vendor.notifications.email', 'vendor.notifications.email-plain'], $message->toArray());

	}

}