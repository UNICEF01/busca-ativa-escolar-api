<?php

namespace BuscaAtivaEscolar\Console\Commands;


use BuscaAtivaEscolar\School;
use BuscaAtivaEscolar\Search\Search;
use DB;
use Illuminate\Support\Str;
use PDO;

class ReindexSchoolById extends Command {

    protected $signature = 'maintenance:reindex_school_by_id';
    protected $description = 'Forces a specific schools to be reindex in ElasticSearch';

    public function handle(Search $search) {

        $schoolID = $this->ask("Enter the school ID: ");

        $pdo = DB::getPDO();
        $mock = new School();

        $stmt = $pdo->prepare("SELECT * FROM schools where schools.id = ".$schoolID);
        $stmt->execute();

        $total = $stmt->rowCount();
        $indexed = 0;

        if(!$total) {
            $this->error("No rows found!");
            return;
        }

        while(($data = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            $mock->fill($data);
            $search->index($mock);
            $indexed++;


            $progress = "[ " . sprintf("%.3f", (($indexed / $total) * 100)) . " % ] \t";
            $memory_usage = "\t mem=" . sprintf("%.2f", (memory_get_usage() / 1024) / 1024) . " MB \t peak=" . sprintf("%.2f", (memory_get_peak_usage() / 1024) / 1024) .' MB';
            $short_name = str_pad(Str::limit($mock->name, 16), 20, " ");
            $this->comment("{$progress} Reindexed: {$mock->id} -> {$mock->uf} / {$short_name} \t {$memory_usage}");
        }

        $this->comment("{$progress} Indexing completed! ({$indexed} out of {$total} indexed) {$memory_usage}");

    }

}
