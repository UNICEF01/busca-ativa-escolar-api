<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\Child;

class SpawnChildAlert extends Command {

    protected $signature = 'debug:spawn_child_alert';
	protected $description = 'Generates a Child/Case/Steps chain by using the same method the API uses';

    public function handle() {
        $tenant = $this->askForTenant();

	    $this->comment("Tenant: {$tenant->name} (ID={$tenant->id})");
	    $confirmed = $this->confirm("Confirm?");

	    if(!$confirmed) {
		    $this->error("User aborted.");
		    return;
	    }

	    $data = [];

	    foreach((new Alerta)->stepFields as $field) {
		    $input = $this->ask("Enter value for: {$field}", false);

		    if(!$input) $this->comment("Leaving '{$field}' empty...");
		    if($input) $data[$field] = $input;
	    }

	    $this->table(['field', 'value'], collect($data)->map(function ($item, $key) {
		    return ['field' => $key, 'value' => $item];
	    }));

	    $confirmed = $this->confirm("Confirm?");

	    if(!$confirmed) {
		    $this->error("User aborted.");
		    return;
	    }

	    $child = Child::spawnFromAlertData($tenant, null, $data);

	    $this->info("Child alert created! ");

	    $this->comment("Child:");
	    dump($child->toArray());

	    $this->comment("Current Case:");
	    dump($child->currentCase->toArray() ?? null);

	    $this->comment("Current Step:");
	    dump($child->currentStep->toArray() ?? null);

    }
}
