<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\User;
use Illuminate\Support\Str;

class RegisterTenant extends Command {

    protected $signature = 'maintenance:register_tenant';
    protected $description = 'Creates a tenant and assigns to a city';

    public function handle() {

	    $city = $this->askForCity();

	    $confirmed = $this->confirm("Create tenant for city: {$city->uf}/{$city->name} (ID={$city->id}, IBGE_ID={$city->ibge_city_id})");

	    if(!$confirmed) {
		    $this->error("User aborted!");
		    return;
	    }

	    $manager = [];
	    $manager['email'] = $this->ask("Enter manager e-mail (a user will be created): ");
	    $manager['name'] = $this->ask("Enter manager name: ");
	    $manager['password'] = $this->ask("Enter manager password: ");
	    $manager['type'] = User::TYPE_GESTOR_OPERACIONAL;
	    $manager['city_id'] = $city->id;

	    $manager = User::register($manager);

	    $this->info("User created: {$manager->id} - {$manager->email}");

	    $now = date('Y-m-d H:i:s');

	    $name = Tenant::generateNameFromCity($city);

	    $tenant = Tenant::create([
	    	'name' => $name,
	    	'name_ascii' => strtolower(Str::ascii($name)),
		    'city_id' => $city->id,
		    'operational_admin_id' => $manager->id,
		    'political_admin_id' => $manager->id,
		    'is_registered' => true,
		    'is_active' => true,
		    'last_active_at' => $now,
		    'registered_at' => $now,
		    'activated_at' => $now,
	    ]);

	    $this->info("Tenant created: {$tenant->id} - {$tenant->name}");

	    $group = Group::createDefaultPrimaryGroup($tenant);
	    $this->info("Tenant primary group created: {$group->id} - {$group->name}");

	    $manager->group_id = $group->id;
	    $manager->tenant_id = $tenant->id;
	    $manager->save();


	    $this->info("Assigned tenant {$tenant->id} and group {$group->id} to manager {$manager->id}");

	    return;

    }
}
