<?php
/**
 * busca-ativa-escolar-api
 * ReindexAllSchools.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 31/01/2017, 10:25
 */

namespace BuscaAtivaEscolar\Console\Commands;


use BuscaAtivaEscolar\School;
use BuscaAtivaEscolar\Search\Search;
use DB;
use Illuminate\Support\Str;
use PDO;

class ReindexAllSchools extends Command {

	protected $signature = 'maintenance:reindex_all_schools';
	protected $description = 'Forces all schools in the system to be reindex in ElasticSearch';

	public function handle(Search $search) {

		$pdo = DB::getPDO();
		$mock = new School();

		$stmt = $pdo->prepare("SELECT * FROM schools");
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