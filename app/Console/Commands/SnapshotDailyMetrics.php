<?php
/**
 * busca-ativa-escolar-api
 * SnapshotDailyMetrics.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 10/02/2017, 18:56
 */

namespace BuscaAtivaEscolar\Console\Commands;


use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Reports\Reports;

class SnapshotDailyMetrics extends Command
{

    protected $signature = 'snapshot:daily_metrics {date?}';
    protected $description = 'Builds the daily metrics snapshot in ElasticSearch';
    private $rel;

    public function handle(Reports $reports)
    {
        $this->rel = $reports;

        $today = $this->argument('date') ?? date('Y-m-d');

        Child::with(['currentCase', 'submitter', 'city'])->chunk(500, function ($children) use ($today) {

            //$this->info("[index] Building children index: {$today}...");

            foreach ($children as $child) {
                if (!empty($child->currentCase)) {
                    $this->comment("[index:{$today}] Child #{$child->id} - {$child->name}");
                    $this->rel->buildSnapshot($child, $today);
                }
            }
        });


        //$this->info("[index] Index built!");

    }

}