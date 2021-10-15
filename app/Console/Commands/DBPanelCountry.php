<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;
use BuscaAtivaEscolar\StateSignup;
use BuscaAtivaEscolar\TenantSignup;
use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\PanelCountry;

class DBPanelCountry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:country';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate panel country table';

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
        PanelCountry::truncate();
        $panel = new PanelCountry;

        $panel->num_tenants = Tenant::query()
            ->count();

        $panel->num_ufs = StateSignup::query()
            ->count();

        $panel->num_signups = TenantSignup::query()
            ->count();

        $panel->num_pending_setup = TenantSignup::query()
            ->where('is_approved', '=', 1)
            ->where('is_provisioned', '=', 0)
            ->count();

        $panel->num_alerts = Alerta::query()
            ->accepted()
            ->count();

        $panel->num_pending_alerts = Child::whereHas('alert', function ($query) {
            $query->where('alert_status', '=', 'pending');
        })
            ->pending()
            ->count();

        $panel->num_rejected_alerts = Child::whereHas('alert', function ($query) {
            $query->where('alert_status', '=', 'rejected');
        })
            ->rejected()
            ->count();

        $panel->num_total_alerts = ChildCase::query()
            ->count();

        $panel->num_cases_in_progress = Child::with(['currentCase'])
            ->hasCaseInProgress()
            ->count();

        $panel->num_children_reinserted = Child::query()
            ->whereIn('child_status', [Child::STATUS_IN_SCHOOL, Child::STATUS_OBSERVATION])
            ->count();

        $panel->num_pending_signups = TenantSignup::query()
            ->whereNull('judged_by')
            ->count();

        $panel->num_pending_state_signups = StateSignup::query()
            ->whereNull('judged_by')
            ->count();

        $panel->num_children_in_school = Child::query()
            ->where('child_status', '=', Child::STATUS_IN_SCHOOL)
            ->count();

        $panel->num_children_in_observation = Child::query()
            ->where('child_status', '=', Child::STATUS_OBSERVATION)
            ->count();

        $panel->num_children_out_of_school = Child::query()
            ->where('child_status', '=', Child::STATUS_OUT_OF_SCHOOL)
            ->where('alert_status', '=', Child::ALERT_STATUS_ACCEPTED)
            ->count();

        $panel->num_children_cancelled = Child::query()
            ->where('child_status', '=', Child::STATUS_CANCELLED)
            ->where('alert_status', '=', Child::ALERT_STATUS_ACCEPTED)
            ->count();

        $panel->num_children_transferred = Child::query()
            ->where('child_status', '=', Child::STATUS_TRANSFERRED)
            ->count();

        $panel->num_children_interrupted = Child::query()
            ->where('child_status', '=', Child::STATUS_INTERRUPTED)
            ->count();

        $panel->save();
    }
}
