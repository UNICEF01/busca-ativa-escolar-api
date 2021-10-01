<?php

/**
 * busca-ativa-escolar-api
 * StateSignupController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/08/2017, 20:54
 */

namespace BuscaAtivaEscolar\Http\Controllers\Tenants;


use Auth;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\IBGE\UF;
use BuscaAtivaEscolar\StateSignup;
use BuscaAtivaEscolar\User;
use BuscaAtivaEscolar\Utils;
use BuscaAtivaEscolar\Mail\StateManagerNotification;
use BuscaAtivaEscolar\LGPD\Interfaces\ILgpd;
use Illuminate\Support\Facades\Mail;

class StateSignupController extends BaseController
{
	protected $lgpdService;

	public function __construct(ILgpd $lgpdService)
	{
		$this->lgpdService = $lgpdService;
	}

	public function register()
	{
		$data = request()->all();

		if (!isset($data['uf'])) return $this->api_failure('missing_uf');

		$uf = UF::getByCode($data['uf']);

		if (!$uf) return $this->api_failure('invalid_uf');

		$existingSignUp = StateSignup::where('uf', $uf->code)->first();

		if ($existingSignUp && $existingSignUp->is_approved) return $this->api_failure('state_already_registered');
		if ($existingSignUp) return $this->api_failure('signup_in_progress');

		try {

			$validator = StateSignup::validate($data);

			if ($validator->fails()) {
				return $this->api_failure('invalid_input', $validator->failed());
			}

			if (User::checkIfExists($data['admin']['email'])) {
				return $this->api_failure('admin_email_in_use');
			}

			if (User::checkIfExists($data['coordinator']['email'])) {
				return $this->api_failure('coordinator_email_in_use');
			}

			$signup = StateSignup::createFromForm($data);

			$message = new StateManagerNotification($signup);
			Mail::to($data['admin']['email'])->send($message);
			//LGPD
			/*$this->lgpdMailService->saveMail([
				'plataform_id' => $signup->id,
				'mail' => $data['admin']['email']
			]);*/

			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function get_pending()
	{
		$pending = StateSignup::query()->with(['admin', 'coordinator']);

		$sort = request('sort', []);
		$filter = request('filter', []);
		$max = request('max', null);

		StateSignup::applySorting($pending, $sort);

		switch ($filter['status']) {
			case "all":
				$pending->withTrashed();
				break;
			case "rejected":
				$pending->withTrashed()->whereNotNull('deleted_at')->where('is_approved', 0);
				break;
			case "approved":
				$pending->where('is_approved', 1);
				break;
			case "pending":
			default:
				$pending->where('is_approved', 0);
				break;
		}

		if (isset($filter['created_at']) && strlen($filter['created_at']) > 0) {
			$numDays = intval($filter['created_at']);
			$cutoffDate = Carbon::now()->addDays(-$numDays);

			$pending->where('created_at', '>=', $cutoffDate->format('Y-m-d H:i:s'));
		}

		$pending = $max ? $pending->paginate($max) : $pending->get();
		$meta = $max ? Utils::buildPaginatorMeta($pending) : null;

		return response()->json(['data' => $max ? $pending->items() : $pending, 'meta' => $meta]);
	}

	public function approve(StateSignup $signup)
	{
		try {

			if (!$signup) return $this->api_failure('invalid_signup_id');

			$signup->approve(Auth::user());

			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function reject(StateSignup $signup)
	{
		try {

			if (!$signup) return $this->api_failure('invalid_signup_id');

			$signup->reject(Auth::user());

			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function resendNotification(StateSignup $signup)
	{
		try {

			if (!$signup) return $this->api_failure('invalid_signup_id');

			$signup->sendNotification();

			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function updateRegistrationEmail(StateSignup $signup)
	{
		try {

			if (!$signup) return $this->api_failure('invalid_signup_id');
			if (!in_array(request('type'), ['admin', 'coordinator'])) return $this->api_failure('invalid_email_type');

			$signup->updateRegistrationEmail(request('type'), request('email'));

			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function checkIfAvailable()
	{
		$uf = request('uf');

		$signup = StateSignup::where('uf', $uf)->first();

		if ($signup) return $this->api_success(['is_available' => false, 'signup_id' => $signup->id]);

		return $this->api_success(['is_available' => true]);
	}

	public function accept(StateSignup $signup)
	{
		try {

			if (!$signup) return $this->api_failure('invalid_signup_id');

			if ($this->lgpdService->findLgpd($signup->id)) {
				return response()->json(['status' => 500, 'error' => 'lgpd assigned']);
			}
			//LGPD
			$this->lgpdService->saveLgpd([
				'plataform_id' => $signup->id,
				'name' => $signup->uf,
				'ip_addr' => request()->ip()
			]);

			/*$this->lgpdMailService->updateMail(
				$signup->id,
				$signup->data['admin']['email']
			);*/

			return response()->json(['status' => 'ok', 'signup_id' => $signup->id]);
		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}
}
