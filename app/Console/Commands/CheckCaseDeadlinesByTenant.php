<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\CaseSteps\CaseStep;
use BuscaAtivaEscolar\Child;
use Carbon\Carbon;

class CheckCaseDeadlinesByTenant extends Command
{

    protected $signature = 'workflow:check_case_deadlines_by_tenant';
    protected $description = 'Checks the case deadlines of a specific tenant and updates the status on the database';

    public function handle()
    {

        $tenant_id = $this->ask('Informe o ID do Tenant:');

        Child::where('tenant_id', $tenant_id)->chunk(500, function ($children) {

            $today = Carbon::today();

            foreach ($children as $child) {

                $step = $child->currentStep; /* @var $step CaseStep */

                if (!$step || !$child->tenant)
                    continue;

                if (!$child->alert_status === Child::ALERT_STATUS_ACCEPTED)
                    continue;

                $stepDeadline = $child->tenant->getDeadlineFor($step->getSlug());

                $this->comment($stepDeadline);

                $currentStatus = $child->deadline_status;

                if ($step->isLate($today, $stepDeadline)) {
                    $newStatus = 'late';
                } else {
                    $newStatus = 'normal';
                }

                if ($child->child_status === Child::STATUS_CANCELLED || $child->child_status === Child::STATUS_IN_SCHOOL) {
                    $newStatus = 'normal';
                }

                if ($step->getSlug() === "gestao_do_caso") {
                    $newStatus = 'normal';
                }

                $this->comment("Processing: {$child->id}: {$stepDeadline} \t Etapa: {$step->getSlug()} days \t {$step->started_at} \t {$currentStatus} -> {$newStatus}");


                $child->update(['deadline_status' => $newStatus]);
            }

        });


    }

}