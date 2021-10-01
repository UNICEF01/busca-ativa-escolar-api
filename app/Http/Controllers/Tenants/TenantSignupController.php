<?php

/**
 * busca-ativa-escolar-api
 * TenantSignupController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/12/2016, 21:09
 */

namespace BuscaAtivaEscolar\Http\Controllers\Tenants;


use Auth;
use BuscaAtivaEscolar\Attachment;
use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\ElectedMayor;
use BuscaAtivaEscolar\Exceptions\ValidationException;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Mail\MayorSignupNotification;
use BuscaAtivaEscolar\TenantSignup;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\User;
use BuscaAtivaEscolar\Utils;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Excel as ExcelB;
use BuscaAtivaEscolar\Exports\TenantSignupExport;
use BuscaAtivaEscolar\LGPD\Interfaces\ILgpd;
use Illuminate\Http\Request;

class TenantSignupController extends BaseController
{

	const PERMITED_FILES_MIME_TYPES = [
		'image/jpeg',
		'image/png'
	];
	private $excel;
	protected $lgpdService;
	public function __construct(ExcelB $excel, ILgpd $lgpdService)
	{
		$this->excel = $excel;
		$this->lgpdService = $lgpdService;
	}

	public function register()
	{
		$data = request()->all();

		if (!isset($data['city_id'])) return $this->api_failure('missing_city_id');

		$city = City::find($data['city_id']);

		if (!$city) return $this->api_failure('invalid_city');

		$existingTenant = Tenant::where('city_id', $city->id)->first();
		$existingSignUp = TenantSignup::where('city_id', $city->id)->first();

		if ($existingTenant) return $this->api_failure('tenant_already_registered');
		if ($existingSignUp) return $this->api_failure('signup_in_progress');

		try {

			$validator = TenantSignup::validate($data);

			if ($validator->fails()) {
				return $this->api_failure('invalid_input', $validator->failed());
			}

			if (User::checkIfExists($data['admin']['email'])) {
				return $this->api_failure('political_admin_email_in_use');
			}

			$signup = TenantSignup::createFromForm($data);

			$message = new MayorSignupNotification($signup);
			Mail::to($data['mayor']['email'])->send($message);

			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function get_pending()
	{
		$pending = TenantSignup::with('city');

		$sort = request('sort', []);
		$filter = request('filter', []);
		$max = request('max', null);

		TenantSignup::applySorting($pending, request('sort'));

		if (isset($filter['city_name']) && strlen($filter['city_name']) > 0) {
			$pending->whereHas('city', function ($sq) use ($filter) {
				return $sq->where('name_ascii', 'REGEXP', Str::ascii($filter['city_name']));
			});
		}

		if (isset($filter['city_uf']) && strlen($filter['city_uf']) > 0) {
			$pending->whereHas('city', function ($sq) use ($filter) {
				return $sq->where('uf', 'REGEXP', Str::ascii($filter['city_uf']));
			});
		}

		switch ($filter['status']) {
			case "all":
				$pending->withTrashed();
				break;
			case "rejected":
				$pending->withTrashed()->whereNotNull('deleted_at')->where('is_approved', 0);
				break;
			case "canceled":
				$pending->withTrashed()->whereNotNull('deleted_at')->where('is_approved', 1);
				break;
			case "pending_approval":
				$pending->where('is_approved', 0);
				break;
			case "pending_setup":
				$pending->where('is_approved', 1)->where('is_provisioned', 0);
				break;
			case "active":
				$pending->where('is_approved', 1)->where('is_provisioned', 1);
				break;
			case "pending":
			default:
				break;
		}

		if (isset($filter['created_at']) && strlen($filter['created_at']) > 0) {
			$numDays = intval($filter['created_at']);
			$cutoffDate = Carbon::now()->addDays(-$numDays);

			$pending->where('created_at', '>=', $cutoffDate->format('Y-m-d H:i:s'));
		}

		$columns = (isset($sort['cities.name:city_id'])) ? ['tenant_signups.*', 'cities.name'] : ['*'];

		$pending = $max ? $pending->paginate($max, $columns) : $pending->get($columns);
		$meta = $max ? Utils::buildPaginatorMeta($pending) : null;

		$pending->each(function (&$item) { /* @var $item TenantSignup */
			if ($item->is_approved && !$item->is_provisioned) {
				$item->provision_url = env('APP_PANEL_URL') . "/admin_setup/" . $item->id . '?token=' . $item->getURLToken();
			}
		});

		return response()->json(['data' => $max ? $pending->items() : $pending, 'meta' => $meta]);
	}

	public function export_signups()
	{
		$status = request('status');
		$city_name = request('city_name');
		$city_uf = request('city_uf');
		$created_at = request('created_at');
		(new TenantSignupExport($status, $city_name, $city_uf, $created_at))->store('attachments/buscaativaescolar_adesoes.xlsx');
		return response()->download(storage_path("app/attachments/buscaativaescolar_adesoes.xlsx"));
	}

	public function get_via_token(TenantSignup $signup)
	{
		$token = request('token');
		$validToken = $signup->getURLToken();

		if (!$token) return $this->api_failure('invalid_token');
		if ($token !== $validToken) return $this->api_failure('token_mismatch');
		if (!$signup->is_approved) return $this->api_failure('not_approved');
		if ($signup->is_provisioned) return $this->api_failure('already_provisioned');
		$cityId = $signup['original']['city_id'];
		$city = $signup->getCitybyId($cityId);
		$signup->setAttribute('city', $city);

		/*
         * Update da readesao. Exibicao da lista de coordenadores
         * baseado na última adesão que existia na plataforma
         * data setada manualmente
         */
		$lastTenant = Tenant::onlyTrashed()
			->where([
				['deleted_at', '>', '2020-11-17 00:00:00'],
				['city_id', '=', $signup->city_id]
			])->latest('created_at')
			->first();

		$lastCoordinators = null;

		if ($lastTenant != null) {

			$lastCoordinators = User::onlyTrashed()
				->with('group')
				->where([
					['tenant_id', '=', $lastTenant->id],
					['type', '=', User::TYPE_GESTOR_OPERACIONAL]
				])->get();
		}

		$signup->setAttribute('last_tenant', $lastTenant);
		$signup->setAttribute('last_coordinators', $lastCoordinators);

		return response()->json($signup);
	}

	public function approve(TenantSignup $signup)
	{
		try {

			if (!$signup) return $this->api_failure('invalid_signup_id');

			$signup->approve(Auth::user());
			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function accept(TenantSignup $signup, City $city)
	{
		try {
			$cityName = $city->findByID($signup->city_id);

			if (!$signup) return $this->api_failure('invalid_signup_id');

			if ($this->lgpdService->findLgpd($signup->id)) {
				return response()->json(['status' => 500, 'error' => 'lgpd assigned']);
			}

			//LGPD
			$this->lgpdService->saveLgpd([
				'plataform_id' => $signup->id,
				'name' => $cityName->name,
				'ip_addr' => request()->ip()
			]);

			$signup->accept();
			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function reject(TenantSignup $signup)
	{
		try {

			if (!$signup) return $this->api_failure('invalid_signup_id');

			$signup->reject(Auth::user());
			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function resendNotification(TenantSignup $signup)
	{
		try {

			if (!$signup) return $this->api_failure('invalid_signup_id');
			if ($signup->is_provisioned) return $this->api_failure('tenant_already_provisioned');

			$signup->sendNotification();
			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function updateRegistrationEmail(TenantSignup $signup)
	{
		try {

			if (!$signup) return $this->api_failure('invalid_signup_id');
			if (!in_array(request('type'), ['admin', 'mayor'])) return $this->api_failure('invalid_email_type');

			$signup->updateRegistrationEmail(request('type'), request('email'));

			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function complete(TenantSignup $signup)
	{
		$token = request('token');
		$validToken = $signup->getURLToken();

		if (!$token) return $this->api_failure('invalid_token');
		if ($token !== $validToken) return $this->api_failure('token_mismatch');
		if (!$signup->is_approved) return $this->api_failure('not_approved');
		if ($signup->is_provisioned) return $this->api_failure('already_provisioned');

		$politicalAdmin = request('political', []);
		$operationalAdmin = request('operational', []);

		$lastTenant = request('lastTenant', null);
		$lastCoordinators = request('lastCoordinators', []);
		$isNecessaryNewCoordinator = request('isNecessaryNewCoordinator', false);

		if ($isNecessaryNewCoordinator) {
			if (trim(strtolower($politicalAdmin['email'])) === trim(strtolower($operationalAdmin['email']))) {
				return $this->api_failure("admin_emails_are_the_same");
			}
		}

		foreach ($lastCoordinators as $key => $coordinator) {
			if (array_key_exists('active', $coordinator)) {
				if ($coordinator['active'] == false) {
					unset($lastCoordinators[$key]);
				}
			}
			if (!array_key_exists('active', $coordinator)) {
				unset($lastCoordinators[$key]);
			}
		}

		foreach ($lastCoordinators as $coordinator) {
			//emails iguais e ultimo coordenador reativado
			if (trim(strtolower($politicalAdmin['email'])) === trim(strtolower($coordinator['email']))) {
				return $this->api_failure("coordinator_emails_are_the_same");
			}
			if ($isNecessaryNewCoordinator) {
				if (trim(strtolower($operationalAdmin['email'])) === trim(strtolower($coordinator['email']))) {
					return $this->api_failure("coordinator_emails_are_the_same");
				}
			}
		}

		try {

			if ($lastTenant == null) {
				$tenant = Tenant::provision($signup, $politicalAdmin, $operationalAdmin);
			} else {
				$tenant = Tenant::recovere($signup, $politicalAdmin, $operationalAdmin, $lastTenant, $lastCoordinators);
			}
			$this->lgpdService->updateLgpd(array('plataform_id' => $signup->tenant_id), $signup->id);

			return response()->json(['status' => 'ok', 'tenant_id' => $tenant->id]);
		} catch (ValidationException $ex) {
			if ($ex->getValidator()) return $this->api_validation_failed($ex->getReason(), $ex->getValidator());
			return $this->api_failure($ex->getReason());
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function completeSetup()
	{

		$tenant = Auth::user()->tenant;

		if (!$tenant) return $this->api_failure('user_has_no_tenant');

		$tenant->is_setup = true;
		$tenant->save();

		return response()->json(['status' => 'ok']);
	}

	public function uploadfile()
	{
		$file = request()->file('file');
		if (!in_array($file->getMimeType(), self::PERMITED_FILES_MIME_TYPES)) {
			return response()->json(["reason" => "Arquivo inválido",  "status" => "error"], 400);
		}
		$attachment = Attachment::createFromImageTituloEleitor($file, "Titulo de eleitor de prefeito - " . date('Y-m-d H:i:s'));
		return response()->json(['status' => 'ok', 'link' => $attachment->getURLPublic()]);
	}

	public function getMayorByCpf()
	{
		$cpf = request('cpf');
		if (!$cpf) return $this->api_failure('invalid_token');
		$electedMayor = ElectedMayor::where('cpf', '=', $cpf)->first();
		if ($electedMayor != null) {
			return response()->json(['status' => 'ok', 'mayor' => $electedMayor]);
		} else {
			return response()->json(['status' => 'not_found', 'mayor' => null]);
		}
	}

	public function get_user_via_token(User $user)
	{
		$token = request('token');
		$validToken = $user->getURLToken();

		if (!$token) return $this->api_failure('invalid_token');
		if ($token !== $validToken) return $this->api_failure('token_mismatch');
		if ($user->lgpd) return $this->api_failure('lgpd_already_accepted');

		return response()->json($user);
	}

	public function confirm_user(User $user)
	{
		$token = request('token');
		$validToken = $user->getURLToken();

		$input = request()->all();

		if (!$token) return $this->api_failure('invalid_token');
		if ($token !== $validToken) return $this->api_failure('token_mismatch');
		if ($user->lgpd) return $this->api_failure('lgpd_already_accepted');

		if (trim($input['user']['email']) != $user->email) {
			if (User::checkIfExists($input['user']['email'])) {
				return $this->api_failure('email_already_used');
			}
		}

		if (!isset($input['user']['password'])) {
			return $this->api_failure('invalid_password');
		}

		$validation = $user->validate($input['user'], false, false, false);

		$input['user']['password'] = password_hash($input['user']['password'], PASSWORD_DEFAULT);

		if ($validation->fails()) {
			return $this->api_validation_failed('validation_failed', $validation);
		}

		$user->fill($input['user']);

		$user->save();

		return response()->json(['status' => 'ok', 'updated' => $input['user']]);
	}
}
