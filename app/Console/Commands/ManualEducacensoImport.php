<?php
/**
 * busca-ativa-escolar-api
 * ManualEducacensoImport.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2018
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 28/09/2018, 13:59
 */

namespace BuscaAtivaEscolar\Console\Commands;


use BuscaAtivaEscolar\Importers\EducacensoXLSImporter;
use BuscaAtivaEscolar\ImportJob;
use BuscaAtivaEscolar\User;
use Log;

class ManualEducacensoImport extends Command {

	protected $signature = 'maintenance:manual_educacenso_import';
	protected $description = 'Manually imports an Educacenso XLS file';

	public function handle() {

		ini_set('memory_limit', '4G');

		$filePath = $this->ask('Digite o caminho do arquivo: ');
		$tenantID = $this->ask('Digite o ID do tenant: ');

		$botUser = User::find(User::ID_EDUCACENSO_BOT);

		Log::listen(function ($level, $message, array $context = []) {
			$memory = sprintf("%.3f", memory_get_usage(true) / 1024);
			$this->comment("LOG:{$level}\t (mem= {$memory} KiB) \t" . $message);
		});

		$job = ImportJob::create([
			'type' => 'educacenso_xls',
			'status' => ImportJob::STATUS_PENDING,
			'user_id' => $botUser->id,
			'tenant_id' => $tenantID,
			'path' => $filePath,
			'offset' => 0,
			'total_records' => 0,
		]);

		$importer = new EducacensoXLSImporter();
		$importer->handle($job);

		$this->info('Job completed!');

	}

}