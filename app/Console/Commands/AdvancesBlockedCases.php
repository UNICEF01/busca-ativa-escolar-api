<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\ChildCase;
use Illuminate\Console\Command;

class AdvancesBlockedCases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:advances_blocked_cases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Avança casos em etapas já concluídas e travados';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * 
     * It goes through all the cases, for each case it accesses the respective step, checks if it is complete 
     * and if it does not correspond to step number 90 (last observation) and advances to the next step.
     * The idea is to force the stage to advance. No case can be in a step that is complete other than step_index 90
     *
     * @return int
     */
    public function handle()
    {
        $chunkSize = 500;
        $count = 0;

        ChildCase::chunk($chunkSize, function ($cases) use (&$count) {

            foreach ($cases as $case) {

                if ($case->child) {

                    $stepClassName = $case->current_step_type;

                    if (class_exists($stepClassName)) {

                        $instanceStep = app($stepClassName);
                        $stepCase = $instanceStep->find($case->current_step_id);
                        if ($stepCase && $stepCase->is_completed && $stepCase->step_index < 90) {
                            $case->advanceToNextStep();
                            $count++;
                        }
                    } else {
                        $this->comment("Class doesn't exist");
                    }
                }
            }
        });

        $this->comment("Number of blocked cases: " . $count);
    }
}
