<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Mail\MayorSignupConfirmation;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\TenantSignup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendEmailToTenantsWithTerms extends Command
{

    protected $qtdTenantsQuery1 = 0;
    protected $qtdTenantsQuery2 = 0;
    protected $mayorWithoutEmail_Approved = 0;
    protected $mayorWithoutEmail_NotApproved = 0;
    protected  $mayorsWithoutEmail_Approved = [];
    protected  $mayorsWithoutEmail_NotApproved = [];

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

        DB::table('tenants')
            ->orderBy('id')
            ->whereNull('deleted_at')
            ->chunk(100, function ($tenants) {
            foreach ($tenants as $tenant) {

                $tenantSignup = TenantSignup::where([
                    ['is_approved_by_mayor', '=', 1],
                    ['tenant_id', '=', $tenant->id]
                ])->get()->first();

                if ($tenantSignup) {

                    //existe o email na propiedade mayor?
                    if(array_key_exists("email",$tenantSignup->data['mayor'])){

                        $this->comment(" ID - TENANT SIGNUP: ".$tenantSignup->id." | EMAIL PREFEITO - TENANT SIGNUP: ".strtolower($tenantSignup->data['mayor']['email']));

                    }else{
                        array_push($this->mayorsWithoutEmail_Approved, $tenantSignup->id);
                        $this->mayorWithoutEmail_Approved++;
                    }

                    $this->qtdTenantsQuery1++;
                }

            }
        });

        //Query 2

        DB::table('tenants')
            ->orderBy('id')
            ->whereNull('deleted_at')
            ->chunk(100, function ($tenants) {
            foreach ($tenants as $tenant) {

                $tenantSignup = TenantSignup::where([
                    ['is_approved_by_mayor', '=', 0],
                    ['tenant_id', '=', $tenant->id]
                ])->get()->first();

                if ($tenantSignup) {

                    //existe o email na propiedade mayor?
                    if(array_key_exists("email",$tenantSignup->data['mayor'])){

                        $this->comment(" ID - TENANT SIGNUP: ".$tenantSignup->id." | EMAIL PREFEITO - TENANT SIGNUP: ".strtolower($tenantSignup->data['mayor']['email']));

                    }else{
                        array_push($this->mayorsWithoutEmail_NotApproved, $tenantSignup->id);
                        $this->mayorWithoutEmail_NotApproved++;
                    }

                    $this->qtdTenantsQuery2++;

                }

            }
        });

        $this->comment(DB::table('tenants')->whereNull('deleted_at')->count());
        $this->comment($this->qtdTenantsQuery1." tenants signups approved by mayor.");
        $this->comment($this->qtdTenantsQuery2." tenants signups not approved by mayor.");

        $this->comment("---------------------------------------------------------------");

        $this->comment($this->mayorWithoutEmail_Approved." prefeitos sem email na query 1 - aprovados");
        $this->comment($this->mayorWithoutEmail_NotApproved." prefeitos sem email na query 2 - não aprovados");

        $this->comment("---------------------------------------------------------------");

        $this->comment("IDs prefeitos sem email query 1:");
        $this->comment(implode(" | ", $this->mayorWithoutEmail_Approved));

        $this->comment("IDs prefeitos sem email query 2:");
        $this->comment(implode(" | ", $this->mayorWithoutEmail_NotApproved));

        $this->qtdTenantsQuery1 = 0;
        $this->qtdTenantsQuery2 = 0;
        $this->mayorWithoutEmail_NotApproved = 0;
        $this->mayorWithoutEmail_Approved = 0;

    }
}
