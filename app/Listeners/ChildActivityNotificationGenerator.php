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


use BuscaAtivaEscolar\Child;
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
use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\Notifications\ChildAssigned;
use BuscaAtivaEscolar\Notifications\ChildUpdated;
use BuscaAtivaEscolar\User;
use Log;
use Notification;

class ChildActivityNotificationGenerator
{

    protected function resolveGroupUsers(Child $child)
    {
        /*
        $groupIDs = Group::getGroupIDsByAlertCause($child->tenant, $child->currentCase->alert_cause_id);
        if (is_array($groupIDs) && (sizeof($groupIDs) <= 0)) return null;
        return User::findByGroupIDs($groupIDs);
        */
    }

    public function onAlertSpawned(AlertSpawned $event)
    {

    }

    public function onAlertAccepted(AlertAccepted $event)
    {

    }

    public function onAlertRejected(AlertRejected $event)
    {

    }

    public function onStepUpdated(CaseStepUpdated $event)
    {

    }

    public function onStepAssigned(CaseStepAssigned $event)
    {
        Notification::send($event->user, new ChildAssigned($event->step->child, 'assigned_to_me'));
    }

    public function onStepStarted(CaseStepStarted $event)
    {

    }

    public function onStepCompleted(CaseStepCompleted $event)
    {

    }

    public function onCaseCompleted(ChildCaseCompleted $event)
    {

    }

    public function onCaseInterrupted(ChildCaseInterrupted $event)
    {

    }

    public function onCaseCancelled(ChildCaseCancelled $event)
    {

    }

    public function subscribe($events)
    {
        $events->listen(AlertSpawned::class, 'BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator@onAlertSpawned');
        $events->listen(AlertAccepted::class, 'BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator@onAlertAccepted');
        $events->listen(AlertRejected::class, 'BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator@onAlertRejected');
        $events->listen(CaseStepUpdated::class, 'BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator@onStepUpdated');
        $events->listen(CaseStepAssigned::class, 'BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator@onStepAssigned');
        $events->listen(CaseStepStarted::class, 'BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator@onStepStarted');
        $events->listen(CaseStepCompleted::class, 'BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator@onStepCompleted');
        $events->listen(ChildCaseCompleted::class, 'BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator@onCaseCompleted');
        $events->listen(ChildCaseInterrupted::class, 'BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator@onCaseInterrupted');
        $events->listen(ChildCaseCancelled::class, 'BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator@onCaseCancelled');
    }

}