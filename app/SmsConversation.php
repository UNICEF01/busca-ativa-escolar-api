<?php
/**
 * busca-ativa-escolar-api
 * SmsConversation.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 02/03/2017, 23:34
 */

namespace BuscaAtivaEscolar;


use Auth;
use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\Data\AlertCause;
use BuscaAtivaEscolar\SMS\SMS;
use BuscaAtivaEscolar\SMS\SmsProvider;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SmsConversation extends Model {

	use IndexedByUUID;

	const STEP_CONFIRM = 'confirm';
	const STEP_IDENTIFY = 'identify';
	const STEP_CANCELLED = 'cancelled';
	const STEP_ASK_NAME = 'ask_name';
	const STEP_ASK_MOTHER_NAME = 'ask_mother_name';
	const STEP_ASK_ADDRESS = 'ask_address';
	const STEP_ASK_NEIGHBORHOOD = 'ask_neighborhood';
	const STEP_ASK_CAUSE = 'ask_cause';
	const STEP_COMPLETED = 'completed';

	/* @var $sms SmsProvider */
	protected $sms;

	protected $fillable = [
		'user_id',
		'tenant_id',

		'spawned_child_id',

		'phone_number',

		'conversation_step',

		'received_messages',
		'metadata',
		'alert_fields',
	];

	protected $casts = [
		'received_messages' => 'collection',
		'metadata' => 'array',
		'alert_fields' => 'collection'
	];

	protected $queuedResponses = [];

	protected function getSmsProvider() {
		return app(SmsProvider::class);
	}

	public function user() {
		return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'user_id');
	}

	public function tenant() {
		return $this->hasOne('BuscaAtivaEscolar\Tenant', 'id', 'tenant_id');
	}

	public function spawnedChild() {
		return $this->hasOne('BuscaAtivaEscolar\Child', 'id', 'spawned_child_id');
	}

	public function setStep($step) {
		$this->conversation_step = $step;
		$this->save();

		$this->onStepEnter($step);
	}

	public function queueReply($message) {
		array_push($this->queuedResponses, $message);
	}

	public function reply($message) {
		$this->getSmsProvider()->send($this->phone_number, $message);
	}

	public function sendQueuedReplies() {

		$responses = $this->queuedResponses;

		foreach($responses as $current => $message) {
			$this->reply($message);

			if($current < sizeof($this->queuedResponses)) {
				sleep(2);
			}
		}

		$this->queuedResponses = [];

		return $responses;
	}

	public function onStepEnter($step) {
		switch($step) {
			case self::STEP_CONFIRM:
				$this->queueReply("Voce gostaria de enviar um alerta ao Busca Ativa Escolar (S/N)");
				break;

			case self::STEP_IDENTIFY:
				$this->queueReply("Qual o seu e-mail de cadastro?");
				break;

			case self::STEP_ASK_NAME:
				$this->queueReply("Qual o nome completo da crianca? (nao coloque acentos ou caracteres especiais)");
				break;

			case self::STEP_ASK_MOTHER_NAME:
				$this->queueReply("Qual o nome completo da mae ou responsavel? (nao coloque acentos ou caracteres especiais)");
				break;

			case self::STEP_ASK_ADDRESS:
				$this->queueReply("Qual o logradouro do endereco? (nao coloque acentos ou caracteres especiais)");
				break;

			case self::STEP_ASK_NEIGHBORHOOD:
				$this->queueReply("Qual o bairro? (nao coloque acentos ou caracteres especiais)");
				break;

			case self::STEP_ASK_CAUSE:
				$this->queueReply("Qual o possivel motivo da crianca estar fora da escola? Responda conforme a tabela de motivos de 1 a 16.");

				$i = 1;

				$causeList = collect(AlertCause::getAll())
					->filter(function ($cause) {
						return !$cause->hidden;
					})
					->map(function ($item) use (&$i) {
						return $i++ . ' - ' . Str::ascii($item->label);
					})
					->implode("\n");

				$this->queueReply($causeList);

				break;

			case self::STEP_COMPLETED:
				$this->queueReply("O seu alerta foi recebido com sucesso! Obrigado!");
				break;

			case self::STEP_CANCELLED:
				$this->queueReply("O envio do alerta foi cancelado. Para reiniciar o processo, envie novamente um SMS com a palavra ESCOLA");
				break;

		}
	}

	public function cancel() {
		$this->setStep(self::STEP_CANCELLED);
	}

	public function handleMessage($message) {
		switch($this->conversation_step) {

			case self::STEP_CONFIRM:

				if(strtolower($message) != 's') {
					$this->cancel();
					return;
				}

				$this->setStep(self::STEP_IDENTIFY);

				break;

			case self::STEP_IDENTIFY:

				$email = strtolower(str_replace(' ', '', $message));
				$user = User::where('email', '=', $email )->first();

				if(!$user) {
					$this->queueReply("O email informado nao esta cadastrado. Verifique se digitou corretamente e tente novamente.");
					$this->setStep(self::STEP_IDENTIFY);
					return;
				}

				if(!$user->tenant) {
					$this->queueReply("Gestores nacionais e superusuarios nao podem cadastrar alertas.");
					$this->setStep(self::STEP_IDENTIFY);
					return;
				}

				$this->user_id = $user->id;
				$this->tenant_id = $user->tenant_id;
				$this->alert_fields = [
					'place_city_id' => $user->tenant->city_id,
					'place_city_name' => $user->tenant->city->name,
					'place_uf' => $user->tenant->city->uf,
				];

				$this->save();

				$this->setStep(self::STEP_ASK_NAME);

				break;

			case self::STEP_ASK_NAME:
				if(!$this->validateAndUpdateField('name', $message)) $this->setStep(self::STEP_ASK_NAME);
				$this->setStep(self::STEP_ASK_MOTHER_NAME);
				break;

			case self::STEP_ASK_MOTHER_NAME:
				if(!$this->validateAndUpdateField('mother_name', $message)) $this->setStep(self::STEP_ASK_MOTHER_NAME);
				$this->setStep(self::STEP_ASK_ADDRESS);
				break;

			case self::STEP_ASK_ADDRESS:
				if(!$this->validateAndUpdateField('place_address', $message)) $this->setStep(self::STEP_ASK_ADDRESS);
				$this->setStep(self::STEP_ASK_NEIGHBORHOOD);
				break;

			case self::STEP_ASK_NEIGHBORHOOD:
				if(!$this->validateAndUpdateField('place_neighborhood', $message)) $this->setStep(self::STEP_ASK_NEIGHBORHOOD);
				$this->setStep(self::STEP_ASK_CAUSE);
				break;

			case self::STEP_ASK_CAUSE:

				$causeIndex = intval($message);

				if($causeIndex < 1 || $causeIndex > 16) {
					$this->queueReply('Voce deve indicar um numero de 1 a 16, de acordo com a tabela de motivos.');
					$this->setStep(self::STEP_ASK_CAUSE);
					return;
				}

				$cause = AlertCause::getBySMSIndex($causeIndex);

				if(!$cause || !$cause->id) {
					$this->queueReply('Motivo invalido! Voce deve indicar um numero de 1 a 16, de acordo com a tabela de motivos.');
					$this->setStep(self::STEP_ASK_CAUSE);
					return;
				}

				$this->setField('alert_cause_id', $cause->id);

				$validator = (new Alerta())->validate($this->alert_fields->toArray(), true);

				if($validator->fails()) {
					$this->queueReply("Ocorreu um erro ao validar os dados do alerta. Por favor, tente novamente.");
					$this->setStep(self::STEP_ASK_CAUSE);
					return;
				}

				Auth::setUser($this->user); // Necessary so the observers fire correctly
				$child = Child::spawnFromAlertData($this->tenant, $this->user->id, $this->alert_fields->toArray());

				if(!$child) {
					$this->queueReply("Ocorreu um erro ao gerar o caso relacionado ao alerta!");
					$this->setStep(self::STEP_ASK_CAUSE);
					return;
				}

				$this->spawned_child_id = $child->id;
				$this->save();

				$this->setStep(self::STEP_COMPLETED);

				break;

		}
	}

	protected function validateAndUpdateField($field, $message) {
		$value = Str::ascii($message);

		if(strlen($value) < 1) {
			$this->queueReply("Dados invalidos. Por favor, digite corretamente o campo.");
			return false;
		}

		$this->setField($field, $value);

		return true;
	}

	protected function setField($field, $value) {
		$fields = $this->alert_fields; // Indeference necessary so save() actually replaces the existing data
		$fields[$field] = $value;
		$this->alert_fields = $fields;
		$this->save();
	}

	public function registerRequest($request) {
		if(!is_object($this->received_messages)) {
			$this->received_messages = collect([]);
		}

		$this->received_messages = (clone $this->received_messages)->push($request);

		$this->save();
	}

	// -------------------------------------------------

	public static function handleRequest($request) {
		$provider = app(SmsProvider::class); /* @var $provider SmsProvider */
		$sms = $provider->handle($request);

		$conversation = self::findExisting($sms->number); /* @var $conversation self */

		if(!$conversation) {
			$conversation = self::begin($sms->number);
		} else {
			$conversation->handleMessage($sms->message);
		}

		$conversation->registerRequest($request);

		return $conversation;

	}

	public static function findExisting($phoneNumber) {
		return self::query()
			->where('phone_number', $phoneNumber)
			->whereNotIn('conversation_step', [self::STEP_COMPLETED, self::STEP_CANCELLED])
			->first();
	}

	public static function begin($phoneNumber) {
		$conversation = self::create([
			'user_id' => null,
			'tenant_id' => null,
			'spawned_child_id' => null,
			'phone_number' => $phoneNumber,
			'conversation_step' => 'begin',
			'received_messages' => [],
			'metadata' => null,
			'alert_fields' => []
		]);

		$conversation->setStep(self::STEP_CONFIRM);

		return $conversation;
	}

}