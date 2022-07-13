<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;
use BuscaAtivaEscolar\Child;

class ReindexChildrenTenant extends Command
{
    protected $signature = 'maintenance:reindex_children_by_tenant';
    protected $description = 'Forces all children in the tenant to be reindex in ElasticSearch';

    public function handle()
    {
        $tenant = $this->ask('Tenant?');

        foreach (Child::where('tenant_id', $tenant)->get() as $child) {
            $child->save();
        }

        $this->comment("All children reindexed!");
    }
}
