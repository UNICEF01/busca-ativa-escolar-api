<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendEmailToTenantsWithTerms extends Command
{

    protected $qtdTenantsQuery1 = 0;
    protected $qtdTenantsQuery2 = 0;
    protected $qtdTenantsQuery3 = 0;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:send_emails_to_tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encaminha emails para todos os municípios da plataforma para aceite do termo da LGPD';

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
     * @return int
     */
    public function handle()
    {
        //Query 1
        //Pega todos os tenant_signups não desativados:
        DB::table('tenant_signups')
            ->whereNull('deleted_at')
            ->where('is_approved_by_mayor', '=', 1)
            ->orderBy('id')
            ->chunk(100, function($tenantSignups) {
                foreach ($tenantSignups as $tenantSignup) {

                    //Pega o tenant ativo relacionado ao tenantSignup:
                    $tenant = Tenant::where('id', '=', $tenantSignup->tenant_id)->get()->first();

                    if($tenant){ $this->qtdTenantsQuery1++; }

                }
            });

        $this->comment("Query 1:".$this->qtdTenantsQuery1);
        $this->qtdTenantsQuery1 = 0;

    }
}
