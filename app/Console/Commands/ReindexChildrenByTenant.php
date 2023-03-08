<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Child;
use Illuminate\Console\Command;

class ReindexChildrenByTenant extends Command
{
    protected $signature = 'maintenance:reindex_children_by_tenant';
    protected $description = 'Forces all children of a specific Tenant in the system to be reindex in ElasticSearch';

    public function handle()
    {

        $tenant_id = $this->ask('Informe o ID do Tenant:');

        Child::where('tenant_id', $tenant_id)->chunk(100, function ($children) {
            foreach ($children as $child) {
                $this->comment("Reindexing: " . ($child->tenant->name ?? '## NO TENANT! ##') . " / {$child->id} -> {$child->name}");
                $child->save();
            }
            $this->comment("chunk");
        });

        $this->comment("All children reindexed!");
    }
}
