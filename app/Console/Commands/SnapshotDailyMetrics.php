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

        $days = [
            '2022-12-29',
            '2022-12-30',
            '2022-12-31',
            '2023-01-01',
            '2023-01-02',
            '2023-01-03'
        ];

        foreach ($days as $day) {

            Child::with(['currentCase', 'submitter', 'city'])->where([['created_at', '<=', '2022-12-28 23:59:59']])->chunk(500, function ($children) use ($day) {

                foreach ($children as $child) {
                    if (!empty($child->currentCase)) {
                        $this->comment("[index:{$day}] Child #{$child->id} - {$child->name}");
                        $this->rel->buildSnapshot($child, $day);
                    }
                }

            });

        }

    }

}