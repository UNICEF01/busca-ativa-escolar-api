<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Child;
use Illuminate\Console\Command;

class ReindexOneChild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:reindex_unique_child';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Forces a specific child in the system to be reindex in ElasticSearch';

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
        $child_id = $this->ask('Qual o ID da crianca?');

        $child = Child::where('id', $child_id)->first();

        $this->comment("Reindexando: " . ($child->tenant->name ?? '## NO TENANT! ##') . " / {$child->id} -> {$child->name}");

        $child->save();
    }
}
