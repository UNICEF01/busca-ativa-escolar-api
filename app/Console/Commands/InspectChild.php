<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\ChildCase;

class InspectChild extends Command {

    protected $signature = 'debug:inspect_child';
    protected $description = 'Inspects a Child-Case-Steps block by child ID';

    public function handle() {

	    $childID = $this->ask("Enter Child ID: ", 'b9d1d8a0-ce23-11e6-98e6-1dc1d3126c4e');

	    $child = Child::findOrFail($childID); /* @var $child Child */
	    $cases = $child->cases; /* @var $cases ChildCase[] */

	    $this->info("Child: {$child->name} (ID={$child->id})");
	    foreach($cases as $caseIndex => $case) {
		    $this->comment("\t Case #{$caseIndex} - {$case->name} (ID={$case->id})");
	    }

	    $this->comment("\t X: See consolidated search document for child");

	    $selectedCase = $this->ask("Enter case #: ", '0');

	    if(strtolower($selectedCase) === 'x') {
	    	$document = $child->buildSearchDocument();

	    	foreach($document as $key => $value) {
	    		$this->info("[{$key}] => " . json_encode($value));
		    }

		    return;
	    }

	    $case = $cases[intval($selectedCase)];

	    $this->info("Case: {$case->name} (ID={$case->id})");
	    $steps = $case->fetchSteps();

	    foreach($steps as $stepIndex => $step) {
		    $this->comment("\t Step #{$stepIndex} - " . ($step->is_completed ? 'COMPLETED' : 'PENDING') . " - {$step->step_type} (ID={$case->id})");
	    }

	    $selectedStep = $this->ask("Enter step #: ", '0');

	    $step = $steps[intval($selectedStep)]; /* @var $step \BuscaAtivaEscolar\CaseSteps\CaseStep */

	    $this->info("Case Step: {$step->step_type} - {$step->id}");
	    foreach($step->stepFields as $field) {
		    $this->comment("\t[{$field}] = '{$step->$field}'");
	    }

    }
}
