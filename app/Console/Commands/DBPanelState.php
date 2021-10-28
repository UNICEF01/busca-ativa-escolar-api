<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\TenantSignup;
use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\PanelState;
use BuscaAtivaEscolar\Tenant;
use Illuminate\Console\Command;
use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\City;


class DBPanelState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:state';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate panel state table';

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
        PanelState::truncate();
        $reg = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO', 'BR'];
        for ($i = 0; $i < count($reg); $i++) {
            $tenantIDs = Tenant::getIDsWithinUF($reg[$i]);
            $cityIDs = City::getIDsWithinUF($reg[$i]);

            $panel = new PanelState;

            $panel->name = $reg[$i];

            $panel->num_tenants = Tenant::query()
                ->where('uf', $reg[$i])->count();

            $panel->num_signups = TenantSignup::query()
                ->whereIn('city_id', $cityIDs)->count();

            $panel->num_pending_setup = TenantSignup::query()
                ->whereIn('city_id', $cityIDs)->where('is_approved', 1)
                ->where('is_provisioned', 0)
                ->count();

            $panel->num_alerts = Alerta::query()
                ->whereIn('tenant_id', $tenantIDs)->notRejected()
                ->count();

            $panel->num_cases_in_progress = ChildCase::query()
                ->whereIn('tenant_id', $tenantIDs)->where('case_status', ChildCase::STATUS_IN_PROGRESS)
                ->count();

            $panel->num_children_reinserted = Child::query()
                ->whereIn('tenant_id', $tenantIDs)->whereIn('child_status', [Child::STATUS_IN_SCHOOL, Child::STATUS_OBSERVATION])
                ->count();

            $panel->num_pending_signups = TenantSignup::query()
                ->whereIn('city_id', $cityIDs)->whereNull('judged_by')
                ->count();

            $panel->num_total_alerts = Alerta::query()
                ->whereIn('tenant_id', $tenantIDs)->count();

            $panel->num_accepted_alerts =  Alerta::query()
                ->whereIn('tenant_id', $tenantIDs)
                ->where('alert_status', '=', Child::ALERT_STATUS_ACCEPTED)
                ->count();

            $panel->num_pending_alerts = Alerta::query()
                ->whereIn('tenant_id', $tenantIDs)->where('alert_status', '=', Child::ALERT_STATUS_PENDING)
                ->count();

            $panel->num_rejected_alerts = Alerta::query()
                ->whereIn('tenant_id', $tenantIDs)->where('alert_status', '=', Child::ALERT_STATUS_REJECTED)
                ->count();

            $panel->num_children_in_school = Child::query()
                ->whereIn('tenant_id', $tenantIDs)->where('child_status', '=', Child::STATUS_IN_SCHOOL)
                ->count();

            $panel->num_children_out_of_school = Child::query()
                ->whereIn('tenant_id', $tenantIDs)->where([['child_status', '=', Child::STATUS_OUT_OF_SCHOOL], ['alert_status', '=', Child::ALERT_STATUS_ACCEPTED]])
                ->count();

            $panel->num_children_in_observation = Child::query()
                ->whereIn('tenant_id', $tenantIDs)->where('child_status', '=', Child::STATUS_OBSERVATION)
                ->count();

            $panel->num_children_cancelled = Child::query()
                ->whereIn('tenant_id', $tenantIDs)->where([['child_status', '=', Child::STATUS_CANCELLED], ['alert_status', '=', Child::ALERT_STATUS_ACCEPTED]])
                ->count();

            $panel->num_children_transferred = Child::query()
                ->whereIn('tenant_id', $tenantIDs)->where([['child_status', '=', Child::STATUS_TRANSFERRED]])
                ->count();

            $panel->num_children_interrupted = Child::query()
                ->whereIn('tenant_id', $tenantIDs)->where([['child_status', '=', Child::STATUS_INTERRUPTED],])
                ->count();


            $panel->save();
        }
    }
}
