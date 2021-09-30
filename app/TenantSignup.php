<?php

/**
 * busca-ativa-escolar-api
 * TenantSignup.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 21/02/2017, 19:06
 */

namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\Mailables\TenantSignupApproved;
use BuscaAtivaEscolar\Mailables\TenantSignupRejected;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use BuscaAtivaEscolar\Traits\Data\Sortable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Messages\MailMessage;
use Jenssegers\Agent\Agent;
use Mail;

/**
 * @property int $id
 * @property string $city_id
 * @property string $tenant_id
 * @property string $judged_by
 *
 * @property bool $is_approved
 * @property bool $is_provisioned
 *
 * @property string $ip_addr
 * @property string $user_agent
 *
 * @property array $data
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property City|null $city
 * @property Tenant|null $tenant
 * @property User|null $judge
 */
class TenantSignup extends Model
{

	use IndexedByUUID;
	use SoftDeletes;
	use Sortable;

	protected $table = "tenant_signups";
	protected $fillable = [
		'city_id',
		'tenant_id',
		'is_approved',
		'is_provisioned',
		'ip_addr',
		'user_agent',
		'data',

		// Sort-only fields
		'cities.name',
		'cities.uf',
		'created_at',
	];

	protected $casts = [
		'is_approved' => 'boolean',
		'is_provisioned' => 'boolean',
		'data' => 'array',
		'is_approved_by_mayor' => 'boolean'
	];

	public function city()
	{
		return $this->hasOne('BuscaAtivaEscolar\City', 'id', 'city_id');
	}

	public function tenant()
	{
		return $this->hasOne('BuscaAtivaEscolar\Tenant', 'id', 'tenant_id')->withTrashed();
	}

	public function judge()
	{
		return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'judged_by')->withTrashed();
	}

	public function getCitybyId($id)
	{
		$cityObj = new \BuscaAtivaEscolar\City;
		$city = $cityObj::findByID($id);
		return $city;
	}

	// -----------------------------------------------------------------------------------------------------------------

	public function approve(User $judge)
	{
		$this->deleted_at = null;
		$this->is_approved = true;
		$this->judged_by = $judge->id;
		$this->save();

		$this->sendNotification();
	}

	public function accept()
	{
		$this->is_approved_by_mayor = true;
		$this->save();
	}

	public function reject(User $judge)
	{
		$this->is_approved = false;
		$this->judged_by = $judge->id;
		$this->save();

		$this->delete();

		$this->sendRejectionNotification();
	}

	public function updateDate($type, $dataTenant)
	{
		if (!in_array($type, ['admin', 'mayor'])) {
			throw new \InvalidArgumentException("Invalid data type: {$type}; valid types are: admin | mayor");
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
		$target = $this->data['admin']['email'];
		Mail::to($target)->send(new TenantSignupApproved($this));
	}

	public function sendRejectionNotification()
	{
		$target = $this->data['admin']['email'];
		Mail::to($target)->send(new TenantSignupRejected($this));
	}

	public function getURLToken()
	{
		return self::generateURLToken($this);
	}


	public function renderStatus($validate)
	{
		if ($validate === 'a') {
			if ($this->is_approved  && $this->is_provisioned && !$this->deleted_at) return 'approved';
			if (!$this->judged_by) return 'pending';
			if ($this->is_approved === false) return 'rejected';
			if ($this->is_provisioned !== true || !$this->tenant) return 'pending_initial_setup';
			//if(!$this->tenant->is_setup) return 'pending_tenant_setup';
			if ($this->tenant->deleted_at || $this->deleted_at) return 'deleted';
		}
		if ($validate === 's') {
			if ($this->is_approved  && $this->is_provisioned && !$this->deleted_at && $this->tenant) {
				if ($this->tenant->last_active_at->diffInDays(Carbon::now()) >= 30) return 'inactive';
				else return 'active';
			}
			return 'null';
		}
	}



	public function toExportArray()
	{
		return [
			'Região' => $this->city ? $this->city->getRegion()->name : null,
			'UF' => $this->city->uf ?? null,
			'Município' => $this->city->name ?? null,
			'Adesão' => trans('signups.status.' . $this->renderStatus('a')),
			'Data adesão' => $this->created_at->format('d/m/Y') ?? null,
			'Status (Considerando últimos 30 dias)' => trans('signups.status.' . $this->renderStatus('s')),
			'Último acesso' => $this->renderStatus('a') === 'approved' ? $this->tenant->last_active_at->format('d/m/Y') : null,
			'Adesão - Gestor - Nome' => $this->data['admin']['name'] ?? null,
			'Adesão - Gestor - E-mail' => $this->data['admin']['email'] ?? null,
			'Adesão - Gestor - Telefone' => $this->data['admin']['phone'] ?? null,
			'Adesão - Prefeito - Nome' => $this->data['mayor']['name'] ?? null,
			'Adesão - Prefeito - E-mail' => $this->data['mayor']['email'] ?? null,
			'Adesão - Prefeito - Telefone' => $this->data['mayor']['phone'] ?? null,
			'Instância - Gestor Operacional - Nome' => $this->tenant->operationalAdmin->name ?? null,
			'Instância - Gestor Operacional - E-mail' => $this->tenant->operationalAdmin->email ?? null,
			'Instância - Gestor Operacional - Telefone' => ($this->tenant && $this->tenant->operationalAdmin) ? $this->tenant->operationalAdmin->getContactPhone() : null,
			'Instância - Gestor Político - Nome' => $this->tenant->politicalAdmin->name ?? null,
			'Instância - Gestor Político - E-mail' => $this->tenant->politicalAdmin->email ?? null,
			'Instância - Gestor Político - Telefone' => ($this->tenant && $this->tenant->politicalAdmin) ? $this->tenant->politicalAdmin->getContactPhone() : null,
			'Data ativação' => $this->tenant ? $this->tenant->created_at->format('d/m/Y') : null,
			'Data exclusão/ rejeição' => $this->deleted_at ? Carbon::createFromFormat('Y-m-d H:i:s', $this->deleted_at)->format('d/m/Y') : null,
			'Instância - Nome' => $this->tenant->name ?? null,
			'Código - IBGE' => $this->tenant->city->ibge_city_id ?? null,
			'Ciclo' => $this->created_at < '2021-01-01 00:00:00.0' ? '2017-2020' : '2021-2024'
		];
	}


	// -----------------------------------------------------------------------------------------------------------------

	public static function generateURLToken(TenantSignup $signup)
	{
		return sha1(env('APP_KEY') . $signup->id . $signup->created_at);
	}

	/**
	 * Creates a new tenant sign-up request
	 * @param array $data The data received from the form
	 * @return string The ID of the sign-up request
	 */
	public static function createFromForm($data)
	{
		$signup = new TenantSignup();

		$signup->is_approved = false;
		$signup->is_provisioned = false;
		$signup->ip_addr = $_SERVER['REMOTE_ADDR'];
		$signup->user_agent = $_SERVER['HTTP_USER_AGENT'];

		$signup->city_id = $data['city_id'];
		$signup->tenant_id = null;
		$signup->data = collect($data)->only(['city_id', 'mayor', 'admin'])->toArray();

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
			'city_id' => 'required',
			'admin.dob' => 'required|date',
			'admin.email' => 'required|email',
			'admin.name' => 'required|string',
			'admin.phone' => 'required|alpha_dash',
			'admin.mobile' => 'nullable|alpha_dash',
			'admin.institution' => 'required|string',
			'admin.position' => 'required|string',
			'mayor.name' => 'required|string',
			'mayor.dob' => 'required|date',
			'mayor.email' => 'email',
			'mayor.phone' => 'required|alpha_dash',
			'mayor.mobile' => 'nullable|alpha_dash',
		]);
	}
}
