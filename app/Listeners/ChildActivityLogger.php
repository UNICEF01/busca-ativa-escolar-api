<?php
/**
 * busca-ativa-escolar-api
 * CaseStepActivityLogger.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 26/01/2017, 15:14
 */

namespace BuscaAtivaEscolar\Listeners;


use BuscaAtivaEscolar\ActivityLog;
use BuscaAtivaEscolar\Events\AlertAccepted;
use BuscaAtivaEscolar\Events\AlertRejected;
use BuscaAtivaEscolar\Events\AlertSpawned;
use BuscaAtivaEscolar\Events\CaseStepAssigned;
use BuscaAtivaEscolar\Events\CaseStepCompleted;
use BuscaAtivaEscolar\Events\CaseStepStarted;
use BuscaAtivaEscolar\Events\CaseStepUpdated;
use BuscaAtivaEscolar\Events\ChildCaseCancelled;
use BuscaAtivaEscolar\Events\ChildCaseCompleted;
use BuscaAtivaEscolar\Events\ChildCaseInterrupted;
use Lang;
use Log;

class ChildActivityLogger {

	public function onAlertSpawned(AlertSpawned $event) {
		ActivityLog::writeEntry($event->case->child, 'alert_spawned', [
			'child_name' => $event->alert->name,
			'child_id' => $event->case->child->id,
			'case_id' => $event->case->id,

			'age_label' => $event->getAgeLabel(),
			'gender_label' => $event->getGenderLabel(),

		], [
			'source' => get_class(),
			'case' => $event->case,
			'alert' => $event->alert,
			'child' => $event->case->child,
			'request' => request()->all()
		]);
	}

	public function onAlertAccepted(AlertAccepted $event) {
		ActivityLog::writeEntry($event->child, 'alert_accepted', [
			'child_name' => $event->child->name,
			'child_id' => $event->child->id,
		], [
			'source' => get_class(),
			'child' => $event->child,
			'request' => request()->all()
		]);
	}

	public function onAlertRejected(AlertRejected $event) {
		ActivityLog::writeEntry($event->child, 'alert_rejected', [
			'child_name' => $event->child->name,
			'child_id' => $event->child->id,
		], [
			'source' => get_class(),
			'child' => $event->child,
			'request' => request()->all()
		]);
	}

	public function onStepUpdated(CaseStepUpdated $event) {
		ActivityLog::writeEntry($event->step->child, 'step_updated', [
			'step_name' => $event->step->getName(),
			'step_id' => $event->step->id,
			'step_type' => $event->step->step_type,
			'child_id' => $event->step->child_id,
			'child_name' => $event->step->child->name,
		], [
			'source' => get_class(),
			'step' => $event->step,
			'data' => $event->data,
			'child' => $event->step->child,
			'request' => request()->all()
		]);
	}

	public function onStepAssigned(CaseStepAssigned $event) {
		ActivityLog::writeEntry($event->step->child, 'step_assigned', [
			'assigned_user_name' => $event->user->name,
			'assigned_user_id' => $event->user->id,
			'assigned_user_type' => $event->user->type,
			'assigned_user_group_id' => $event->user->group_id,
			'step_name' => $event->step->getName(),
			'step_id' => $event->step->id,
			'step_type' => $event->step->step_type,
			'child_name' => $event->step->child->name,
			'child_id' => $event->step->child_id,
		], [
			'source' => get_class(),
			'assigned_user' => $event->user,
			'step' => $event->step,
			'child' => $event->step->child,
			'request' => request()->all()
		]);
	}

	public function onStepStarted(CaseStepStarted $event) {
		ActivityLog::writeEntry($event->step->child, 'step_started', [
			'step_name' => $event->step->getName(),
			'step_id' => $event->step->id,
			'step_type' => $event->step->step_type,

			'child_name' => $event->step->child->name,
			'child_id' => $event->step->child_id,

			'prev_step_name' => $event->prev ? $event->prev->getName() : null,
			'prev_step_id' => $event->prev ? $event->prev->id : null,
			'prev_step_type' => $event->prev ? $event->prev->step_type : null,
		], [
			'source' => get_class(),
			'step' => $event->step,
			'prev_step' => $event->prev,
			'child' => $event->step->child,
			'request' => request()->all()
		]);
	}

	public function onStepCompleted(CaseStepCompleted $event) {
		ActivityLog::writeEntry($event->step->child, 'step_completed', [
			'step_name' => $event->step->getName(),
			'step_id' => $event->step->id,
			'step_type' => $event->step->step_type,

			'child_name' => $event->step->child->name,
			'child_id' => $event->step->child_id,

			'next_step_name' => $event->next ? $event->next->getName() : null,
			'next_step_id' => $event->next ? $event->next->id : null,
			'next_step_type' => $event->next ? $event->next->step_type : null,
		], [
			'source' => get_class(),
			'step' => $event->step,
			'next_step' => $event->next,
			'child' => $event->step->child,
			'request' => request()->all()
		]);
	}

	public function onCaseCompleted(ChildCaseCompleted $event) {
		ActivityLog::writeEntry($event->child, 'status_completed', [
			'child_name' => $event->child->name,
			'child_id' => $event->child->id,
		], [
			'source' => get_class(),
			'child' => $event->child,
			'request' => request()->all()
		]);
	}

	public function onCaseInterrupted(ChildCaseInterrupted $event) {
		ActivityLog::writeEntry($event->child, 'status_interrupted', [
			'child_name' => $event->child->name,
			'child_id' => $event->child->id,
		], [
			'source' => get_class(),
			'child' => $event->child,
			'request' => request()->all()
		]);
	}

	public function onCaseCancelled(ChildCaseCancelled $event) {
		ActivityLog::writeEntry($event->child, 'status_cancelled', [
			'child_name' => $event->child->name,
			'child_id' => $event->child->id,
			'reason' => $event->reason,
			'reason_description' => $this->langHas( 'child_case.cancel_reason.' . $event->reason, 'en' ) ? trans('child_case.cancel_reason.' . $event->reason) : $event->reason,
		], [
			'source' => get_class(),
			'child' => $event->child,
			'request' => request()->all()
		]);
	}

	public function subscribe($events) {
		$events->listen(AlertSpawned::class, 'BuscaAtivaEscolar\Listeners\ChildActivityLogger@onAlertSpawned');
		$events->listen(AlertAccepted::class, 'BuscaAtivaEscolar\Listeners\ChildActivityLogger@onAlertAccepted');
		$events->listen(AlertRejected::class, 'BuscaAtivaEscolar\Listeners\ChildActivityLogger@onAlertRejected');
		$events->listen(CaseStepUpdated::class, 'BuscaAtivaEscolar\Listeners\ChildActivityLogger@onStepUpdated');
		$events->listen(CaseStepAssigned::class, 'BuscaAtivaEscolar\Listeners\ChildActivityLogger@onStepAssigned');
		$events->listen(CaseStepStarted::class, 'BuscaAtivaEscolar\Listeners\ChildActivityLogger@onStepStarted');
		$events->listen(CaseStepCompleted::class, 'BuscaAtivaEscolar\Listeners\ChildActivityLogger@onStepCompleted');
		$events->listen(ChildCaseCompleted::class, 'BuscaAtivaEscolar\Listeners\ChildActivityLogger@onCaseCompleted');
		$events->listen(ChildCaseInterrupted::class, 'BuscaAtivaEscolar\Listeners\ChildActivityLogger@onCaseInterrupted');
		$events->listen(ChildCaseCancelled::class, 'BuscaAtivaEscolar\Listeners\ChildActivityLogger@onCaseCancelled');
	}

	//Funcao para verificar se existe traducao para o termo: default sempre en
    function langHas($phrase, $lang, $default = 'en')
    {
        if(Lang::has($phrase, $lang) == false ){
            return false;
        }

        return true;
    }

}