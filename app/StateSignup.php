<?php

/**
 * busca-ativa-escolar-api
 * StateSignup.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/08/2017, 19:31
 */

namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\Mailables\StateSignupApproved;
use BuscaAtivaEscolar\Mailables\StateSignupRejected;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use BuscaAtivaEscolar\Traits\Data\Sortable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mail;

/**
 * @property int $id
 * @property string $uf
 * @property string $user_id
 * @property string $admin_id
 * @property string $coordinator_id
 * @property bool $is_approved
 * @property string $ip_addr
 * @property string $user_agent
 * @property array $data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property User|null $admin
 * @property User|null $coordinator
 * @property User|null $judged_by
 * @property User[]|Collection|null $users
 */
class StateSignup extends Model
{

	use IndexedByUUID;
	use SoftDeletes;
	use Sortable;

	protected $table = "state_signups";
	protected $fillable = [
		'uf',
		'admin_id',
		'coordinator_id',
		'judged_by',

		'is_approved',

		'ip_addr',
		'user_agent',
		'data',

		// Sort-only fields
		'created_at',
	];

	protected $casts = [
		'is_approved' => 'boolean',
		'data' => 'array',
	];

	public function admin()
	{
		return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'admin_id')->withTrashed();
	}

	public function coordinator()
	{
		return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'coordinator_id')->withTrashed();
	}

	public function judge()
	{
		return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'judged_by')->withTrashed();
	}

	public function users()
	{
		return $this->hasMany(User::class, 'uf', 'uf')->whereIn('type', User::$UF_SCOPED_TYPES)->withTrashed();
	}

	// -----------------------------------------------------------------------------------------------------------------

	public function approve(User $judge)
	{
		$this->is_approved = true;
		$this->judged_by = $judge->id;
		$this->deleted_at = null;
		$this->save();

		$userFields = [
			'dob',
			'cpf',
			'email',
			'name',
			'phone',
			'mobile',
			'institution',
			'position',
		];

		$adminData = collect($this->data['admin'])->only($userFields)->toArray();
		$adminData['uf'] = $this->uf;
		$adminData['tenant_id'] = null;
		$adminData['type'] = User::TYPE_GESTOR_ESTADUAL;
		$adminData['password'] = password_hash($this->data['admin']['password'], PASSWORD_DEFAULT);

		$coordinatorData = collect($this->data['coordinator'])->only($userFields)->toArray();
		$coordinatorData['uf'] = $this->uf;
		$coordinatorData['tenant_id'] = null;
		$coordinatorData['type'] = User::TYPE_COORDENADOR_ESTADUAL;
		$coordinatorData['password'] = password_hash($this->data['coordinator']['password'], PASSWORD_DEFAULT);

		// TODO: validate user data again

		$admin = User::create($adminData);
		$coordinator = User::create($coordinatorData);

		$this->admin_id = $admin->id;
		$this->coordinator_id = $coordinator->id;
		$this->save();

		$this->sendNotification();
	}

	public function reject(User $judge)
	{
		$this->is_approved = false;
		$this->judged_by = $judge->id;
		$this->save();

		$this->delete();

		$this->sendRejectionNotification();
	}

	public function updateRegistrationEmail($type, $email)
	{
		if (!in_array($type, ['admin', 'coordinator'])) {
			throw new \InvalidArgumentException("Invalid e-mail type: {$type}; valid types are: admin | coordinator");
		}

		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		$data = $this->data;
		$data[$type]['email'] = $email;
		$this->data = $data;
		$this->save();
	}

	public function updateDate($type, $dataTenant)
	{
		if (!in_array($type, ['admin', 'coordinator'])) {
			throw new \InvalidArgumentException("Invalid data type: {$type}; valid types are: admin | coordinator");
		}

		$data = $this->data;
		$fieldsSanitezaInteger = ['phone', 'mobile', 'cpf', 'titulo'];

		array_walk($dataTenant['data'], function (&$value, $key) use ($fieldsSanitezaInteger) {
			if (array_key_exists($key, $fieldsSanitezaInteger)) {
				$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
			}
			if ($key === 'email') {
				$value = filter_var($value, FILTER_SANITIZE_EMAIL);
			}
			if ($key === 'dob') {
				if (strpos($value, '/') !== false) {
					$value = explode("/", $value);
					$value = $value[2] . '-' . $value[1] . '-' . $value[0];
				}
			}
		});

		$data[$type] = $dataTenant['data'];

		$this->data = $data;
		$this->save();
	}

	public function sendNotification()
	{
		$adminEmail = $this->data['admin']['email'];
		$coordinatorEmail = $this->data['coordinator']['email'];

		Mail::to([$adminEmail, $coordinatorEmail])->send(new StateSignupApproved($this));
	}

	public function sendRejectionNotification()
	{
		$adminEmail = $this->data['admin']['email'];
		$coordinatorEmail = $this->data['coordinator']['email'];
		Mail::to([$adminEmail, $coordinatorEmail])->send(new StateSignupRejected($this));
	}


	// -----------------------------------------------------------------------------------------------------------------


	/**
	 * Creates a new state sign-up request
	 * @param array $data The data received from the form
	 * @return string The ID of the sign-up request
	 */
	public static function createFromForm($data)
	{
		$signup = new StateSignup();

		$signup->is_approved = false;
		$signup->ip_addr = $_SERVER['REMOTE_ADDR'];
		$signup->user_agent = $_SERVER['HTTP_USER_AGENT'];

		$signup->uf = $data['uf'];
		$signup->data = collect($data)->only(['uf', 'admin', 'coordinator'])->toArray();

		$signup->save();

		return $signup;
	}

	/**
	 * Validates a sign-up request
	 * @param array $data The data received from the form
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	public static function validate($data)
	{
		return validator($data, [
			'uf' => 'required|string',
			'admin.dob' => 'required|date',
			'admin.cpf' => 'required|alpha_dash',
			'admin.email' => 'required|email',
			'admin.name' => 'required|string',
			'admin.phone' => 'required|alpha_dash',
			'admin.mobile' => 'nullable|alpha_dash',
			'admin.institution' => 'required|string',
			'admin.position' => 'required|string',
			'admin.password' => 'required|string',
			'coordinator.dob' => 'required|date',
			'coordinator.cpf' => 'required|alpha_dash',
			'coordinator.email' => 'required|email',
			'coordinator.name' => 'required|string',
			'coordinator.phone' => 'required|alpha_dash',
			'coordinator.mobile' => 'nullable|alpha_dash',
			'coordinator.institution' => 'required|string',
			'coordinator.position' => 'required|string',
			'coordinator.password' => 'required|string',
		]);
	}


	/**
	 * Generates an array to export as XLS
	 * @return array
	 */
	public function toExportArray()
	{
		return [
			'UF' => $this->uf,
			'Data de adesão' => $this->created_at ? $this->created_at->format('d/m/Y') : null,
			'Data exclusão' => $this->deleted_at ?? null,

			'Gestor estadual' => $this->data['admin']['name'] ?? null,

			'Gestor estadual - CPF' => $this->data['admin']['cpf'] ?? null,
			'Gestor estadual - Data de nascimento' => $this->data['admin']['dob'] ?? null,
			'Gestor estadual - Email' => $this->data['admin']['email'] ?? null,
			'Gestor estadual - Telefone' => $this->data['admin']['phone'] ?? null,
			'Gestor estadual - Função' => $this->data['admin']['position'] ?? null,
			'Gestor estadual - Instituição' => $this->data['admin']['institution'] ?? null,

			'Coordenador estadual' => $this->data['coordinator']['name'] ?? null,

			'Coordenador estadual - CPF' => $this->data['coordinator']['cpf'] ?? null,
			'Coordenador estadual - Data de nascimento' => $this->data['coordinator']['dob'] ?? null,
			'Coordenador estadual - Email' => $this->data['coordinator']['email'] ?? null,
			'Coordenador estadual - Telefone' => $this->data['coordinator']['phone'] ?? null,
			'Coordenador estadual - Função' => $this->data['coordinator']['position'] ?? null,
			'Coordenador estadual -Instituição' => $this->data['coordinator']['institution'] ?? null,
		];
	}
}
