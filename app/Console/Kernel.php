<?php

namespace BuscaAtivaEscolar\Console;

use BuscaAtivaEscolar\Console\Commands\Command;
use BuscaAtivaEscolar\Console\Commands\ExportErrorsCasesDisabled;
use BuscaAtivaEscolar\Console\Commands\RemoveAlertsWithoutCases;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Psy\TabCompletion\Matcher\CommandsMatcher;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ImportCitiesFromIBGE::class,
        Commands\ImportSchoolsFromINEP::class,
        Commands\RegisterSuperUser::class,
        Commands\RegisterTenant::class,
        Commands\SpawnChildAlert::class,
        Commands\InspectChild::class,
        Commands\ReindexAllChildren::class,
        Commands\ReindexAllCities::class,
        Commands\ReindexAllSchools::class,
        Commands\SnapshotDailyMetrics::class,
        Commands\TestSchedulingSystem::class,
        Commands\CheckCaseDeadlines::class,
        Commands\SimulateReceivedSMS::class,
        Commands\RebuildGroupCausesMap::class,
        Commands\GenerateSchoolsJSON::class,
        Commands\GenerateStaticDataSQL::class,
        Commands\ManualEducacensoImport::class,
        Commands\AddAndCheckGroupIntoNewCase::class,
        Commands\AddPriorityTenantNewMotive::class,
        Commands\ForceObligatorinessEducationReason::class,
        Commands\DisplayPrimaryGroupsUnrelatedWithAllAlerts::class,
        Commands\ReindexSchoolById::class,
        Commands\RemoveDuplicatedChildsWithoutAlerts::class,
        Commands\ExportErrorsCasesDisabled::class,
        Commands\FixErrorsCasesDisabled::class,
        Commands\TransferNisToAlert::class,
        Commands\FixMapLocationChild::class,
        Commands\ReindexOneChild::class,
        Commands\FixMapLocationChildOutBrazil::class,
        Commands\fixNameChildPesquisa::class,
        Commands\removeInconsistenciesCases::class,
        Commands\SnapshotDailyMetricsConsolidated::class,
        Commands\SnapshotDailyMetricsFullMySQL::class,
        Commands\InsertCancelReasonDailyMetrics::class,
        Commands\PopulateDailyMetricsConsolidated::class,
        Commands\InserirDiaAusenteGraficoRematriculas::class,
        Commands\SendEmailsActualizeFrequency::class,
        Commands\SendEmailToTenantsWithTerms::class,
        Commands\ForceImportEducacensoFile::class,
        Commands\SendEmailLgpd::class,
        Commands\CreateGroupsAndUsersTest::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('debug:test_scheduling_system')->everyMinute();
        $schedule->command('workflow:check_case_deadlines')->dailyAt('20:00');
        $schedule->command('snapshot:daily_metrics')->dailyAt('21:00');
        $schedule->command('snapshot:daily_metrics_consolidated')->dailyAt('22:00');
        $schedule->command('snapshot:daily_metrics_full_mysql')->dailyAt('23:00');
        $schedule->command('maintenance:send_emails_actualize_frequency')->dailyAt('00:00');
    }


    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
