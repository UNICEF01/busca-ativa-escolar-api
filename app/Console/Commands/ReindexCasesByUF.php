<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Tenant;
use Illuminate\Console\Command;

class ReindexCasesByUF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:reindex_cases_by_uf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $uf = $this->ask('Informe o UF:');

        $tenants = Tenant::where('uf', strtoupper($uf))->get()->all();

        foreach ($tenants as $tenant) {
            Child::where('tenant_id', $tenant->id)->chunk(100, function ($children) {
                foreach ($children as $child) {
                    $this->comment("Reindexing: " . ($child->tenant->name ?? '## NO TENANT! ##') . " / {$child->id} -> {$child->name}");
                    $child->save();
                }
                $this->comment("chunk");
            });
        }

        $this->comment("All children reindexed!");
    }
}
