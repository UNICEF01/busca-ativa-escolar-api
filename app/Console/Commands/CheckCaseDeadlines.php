<?php

/**
 * busca-ativa-escolar-api
 * CheckCaseDeadlines.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 16/02/2017, 19:15
 */

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\CaseSteps\CaseStep;
use BuscaAtivaEscolar\Child;
use Carbon\Carbon;

class CheckCaseDeadlines extends Command
{

    protected $signature = 'workflow:check_case_deadlines';
    protected $description = 'Checks the case deadlines and updates the status on the database';

    public function handle()
    {

        Child::chunk(500, function ($children) {

            $today = Carbon::today();

            foreach ($children as $child) {

                $step = $child->currentStep; /* @var $step CaseStep */

                if (!$step || !$child->tenant) continue;

                if (!$child->alert_status === Child::ALERT_STATUS_ACCEPTED) continue;

                $stepDeadline = $child->tenant->getDeadlineFor($step->getSlug());

                if ($step->isLate($today, $stepDeadline)) {
                    $newStatus = 'late';
                } else {
                    $newStatus = 'normal';
                }

                if ($child->child_status === Child::STATUS_CANCELLED || $child->child_status === Child::STATUS_IN_SCHOOL) {
                    $newStatus = 'normal';
                }

                $child->update(['deadline_status' => $newStatus]);
            }
        });
    }
}
