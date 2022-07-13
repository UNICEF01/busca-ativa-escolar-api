<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\User;

class RegisterSuperUser extends Command {

    protected $signature = 'maintenance:register_superuser';
	protected $description = 'Registers a superuser';

    public function handle() {

	    $name = $this->ask("Enter a name for the user:", "Engineering LQDI");
	    $email = $this->ask("Enter an email for the user:", "dev@lqdi.net");
	    $password = $this->ask("Enter a password for the user:", "demo");

	    $user = User::register([
	    	'name' => $name,
		    'email' => $email,
		    'password' => $password,

		    'type' => User::TYPE_SUPERUSER,
		    'tenant_id' => null,
		    'city_id' => null
	    ]);

	    $this->info("User created! ID: {$user->id}");

    }
}
