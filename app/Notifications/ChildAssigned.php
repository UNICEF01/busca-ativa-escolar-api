<?php
/**
 * busca-ativa-escolar-api
 * ChildAssigned.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 09/03/2017, 13:55
 */

namespace BuscaAtivaEscolar\Notifications;

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ChildAssigned extends Notification implements ShouldQueue {

	use Queueable;

	public $child;
	public $relationship;

	public function __construct(Child $child, $relationship = 'all_cases') {
		$this->child = $child;
		$this->relationship = $relationship;
	}

	/**
	 * Returns the list of channels to send this notification through
	 * @param User $notifiable
	 * @return array
	 */
	public function via($notifiable) {
		return $notifiable->getNotificationChannels($this->relationship);
	}

	/**
	 * Renders this notification in e-mail format
	 * @param User $notifiable
	 * @return MailMessage
	 */
	public function toMail($notifiable) {
		return (new MailMessage)
			->subject("[Busca Ativa Escolar] Caso atribuído à você: {$this->child->getShorthandIdentifier()} ")
			->line("Você foi atribuído como responsável pela etapa {$this->child->currentStep->getName()} do caso de {$this->child->getShorthandIdentifier()}.")
			->action('Visualizar caso', $this->child->getViewURL());
	}

	/**
	 * Renders this notification in array format
	 * @param User $notifiable
	 * @return array
	 */
	public function toArray($notifiable) {
		return [
			'tenant_id' => $this->child->tenant_id,
			'title' => "Caso atribuído à você: {$this->child->getShorthandIdentifier()}",
			'type' => 'info',
			'open_url' => $this->child->getViewURL(),
			'child' => [
				'id' => $this->child->id,
				'name' => $this->child->name,
				'gender' => $this->child->gender,
				'age' => $this->child->age,
				'child_status' => $this->child->child_status,
				'current_case_id' => $this->child->current_case_id,
				'current_step_type' => $this->child->current_step_type,
				'current_step_id' => $this->child->current_step_id,
			]
		];
	}

}
