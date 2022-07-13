<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\TenantSignup;
use BuscaAtivaEscolar\User;
use Illuminate\Console\Command;

class CancelAllTenantsWithSigupsAndUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'danger:cancell_all_tenants_with_users_and_signups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ATENÇÃO! COMANDO USADO APENAS PARA A REMOCÃO GERAL DE TODOS OS TENANTS - PROCESSO DE READESÃO';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->comment("Iniciando processo de desativação de todos os tenants");

        Tenant::chunk(100, function($tenants){

            foreach ($tenants as $tenant){

                $this->comment("Desativando ".$tenant->name);

                $users = User::query()->where('tenant_id', $tenant->id);
                $signup = TenantSignup::query()->where('tenant_id', $tenant->id);

                $users->delete();
                $signup->delete();
                $tenant->delete();
            }

        });

        $this->comment("Finalizando o processo de desativação de todos os tenants");

        $this->comment("Iniciando processo de remoção de adesões pendentes");

        //Remove signups não aprovados (sem tenant)
        TenantSignup::whereNull(['tenant_id'])->chunk(100, function($signups){
            foreach ($signups as $signup){
                $this->comment($signup['admin']['institution']);
                $signup->forceDelete();
            }
        });

        $this->comment("Finalizando processo de remoção de adesões pendentes");

    }
}
