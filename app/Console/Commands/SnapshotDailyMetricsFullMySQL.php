<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\CaseSteps\Rematricula;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\DailyMetrics;
use BuscaAtivaEscolar\DailyMetricsConsolidated;
use BuscaAtivaEscolar\Tenant;
use Illuminate\Console\Command;

class SnapshotDailyMetricsFullMySQL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snapshot:daily_metrics_full_mysql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um snapshot de todas as crianças da plataforma diariamente (MySQL)';

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
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        Child::with(['currentCase', 'submitter', 'city'])->chunk(500, function ($children) {

            $today = date('Y-m-d');

            //$this->info("[index] Building children index: {$today}...");

            foreach ($children as $child) {
                if (!empty($child->currentCase)) {
                    $reinsertion_grade = null;
                    $rematricula = Rematricula::where('child_id', '=', $child->id)->first();
                    $reinsertion_grade = $rematricula ? $rematricula->reinsertion_grade : null;

                    //$this->comment("[index:{$today}] Child #{$child->id} - {$child->name}");

                    $tenant = Tenant::withTrashed()->where([ ['id', '=', $child->tenant_id] ])->first();

                    $dailyMetrics = new DailyMetrics(
                        [
                            'tenant_id' => $child->tenant_id,
                            'child_id' => $child->id,
                            'child_status' => $child->child_status,
                            'alert_status' => $child->alert_status,
                            'deadline_status' => $child->deadline_status,
                            'date' => $today,
                            'case_status' => $child->currentCase->case_status,
                            'step_slug' => str_slug($child->currentCase->currentStep->getName(), '_'),
                            'city_id' => $tenant->city->id,
                            'uf' => $tenant->city->uf,
                            'cancel_reason' => $child->currentCase->cancel_reason,
                            'reinsertion_grade' => $reinsertion_grade
                        ]
                    );

                    $dailyMetrics->save();
                }
            }
        });
    }
}
