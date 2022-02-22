<?php
/**
 * busca-ativa-escolar-api
 * StepsController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 08/01/2017, 01:29
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;


use BuscaAtivaEscolar\CaseSteps\CaseStep;
use BuscaAtivaEscolar\CaseSteps\Observacao;
use BuscaAtivaEscolar\CaseSteps\Rematricula;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\ReopeningRequests;
use BuscaAtivaEscolar\Scopes\TenantScope;
use BuscaAtivaEscolar\Search\Search;
use BuscaAtivaEscolar\Serializers\SimpleArraySerializer;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\Transformers\StepTransformer;
use BuscaAtivaEscolar\Transformers\UserTransformer;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;

class StepsController extends BaseController {

	public function show($step_type, $step_id) {

        /* @var $user User */
        $user = \Auth::user();
        $user->type = User::TYPE_GESTOR_NACIONAL;


        try {
			$step = CaseStep::fetch($step_type, $step_id);

            $reopeningRequest = ReopeningRequests::where(
                ['child_id' => $step->child_id]
            )->first();

            if(($step['tenant_id'] == $user['tenant_id']) || ($user['tenant_id'] == $reopeningRequest['tenant_recipient_id'])) {
                return fractal()
                    ->item($step)
                    ->transformWith(new StepTransformer())
                    ->serializeWith(new SimpleArraySerializer())
                    ->parseIncludes(request('with'))
                    ->respond();
            }

		} catch (\Exception $ex) {
			return response()->json(['status' => 'error', 'reason' => $ex->getMessage()]);
		}
	}

	public function update($step_type, $step_id) {
		try {

			$data = request()->all();

			$step = CaseStep::fetch($step_type, $step_id);

			if(isset($step->reinsertion_date_original)) { // TODO: move this to an abstraction
				$data['reinsertion_date_original'] = $step->reinsertion_date_original;
			}

			$validation = $step->validate($data);

            if(!$this->isValidDateForUpdateStep($step)) return $this->api_failure("A etapa ainda não está no perído para cadastro");

			if($validation->fails()) return $this->api_validation_failed('validation_failed', $validation);

			$input = $step->setFields($data);

			return response()->json(['status' => 'ok', 'updated' => $input]);

		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function complete($step_type, $step_id) {

		try {

			$step = CaseStep::fetch($step_type, $step_id);

			if($step->is_completed) return response()->json(['status' => 'error', 'reason' => 'step_already_completed']);
			if(!$step->assigned_user_id) return response()->json(['status' => 'error', 'reason' => 'no_assigned_user']);

			$validation = $step->validate($step->toArray(), true);

			if($validation->fails()) return $this->api_validation_failed('validation_failed', $validation);

			$next = $step->complete();

			// TODO: $step->canBeCompletedBy(Auth::user());

			if(!$next) return response()->json(['status' => 'ok', 'hasNext' => false]);

			return response()->json([
				'status' => 'ok',
				'hasNext' => true,
				'nextStep' => fractal()
					->item($next)
					->transformWith(new StepTransformer())
					->serializeWith(new SimpleArraySerializer())
			]);

		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}

	}

	public function getAssignableUsers($step_type, $step_id) {
		try {
			$step = CaseStep::fetch($step_type, $step_id);

			$query = User::withoutGlobalScope(TenantScope::class)->orderBy('type', 'ASC');
			$query = $step->applyAssignableUsersFilter($query);

			$query->where(function($q) use ($step) {
				return $q // Allow both tenant users or state agent users
					->where('tenant_id', $step->tenant_id);
			});

			$users = $query->get();

			return fractal()
				->collection($users, new UserTransformer(), 'users')
				->serializeWith(new SimpleArraySerializer())
                ->parseIncludes(['group', 'tenant'])
				->respond();

		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

	public function assignUser($step_type, $step_id, Search $search) {
		try {
			$user = User::withoutGlobalScope(TenantScope::class)->findOrFail(request('user_id'));
			$step = CaseStep::fetch($step_type, $step_id);

			$step->assignToUser($user);

			// TODO: fix assignment not reindexing

			return response()->json(['status' => 'ok', 'user' => fractal()->item($user, new UserTransformer())]);

		} catch (\Exception $ex) {
			return $this->api_exception($ex);
		}
	}

    private function isValidDateForUpdateStep(CaseStep $step)
    {
        if($step->step_type == "BuscaAtivaEscolar\CaseSteps\Observacao") {
            $tenant = Tenant::find($step->getAttribute('tenant_id'));
            $deadline = $tenant->getDeadlineFor($step->getSlug());
            $today = Carbon::today();
            return $today->diffInDays($step->started_at) >= $deadline;
        }else{
            return true;
        }
    }

}