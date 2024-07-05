<?php

/**
 * busca-ativa-escolar-api
 * ProcessImportJob.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2018
 *
 * @author Aryel Tupinamba <aryel.tupinamba@lqdi.net>
 *
 * Created at: 14/03/2018, 17:13
 */

namespace BuscaAtivaEscolar\Jobs;


use BuscaAtivaEscolar\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ProcessImportJob implements ShouldQueue
{

	use InteractsWithQueue, Queueable, SerializesModels;

	public $importJob;

	public function __construct(ImportJob $importJob)
	{
		$this->importJob = $importJob;
	}

	/**
	 * Determine the time at which the job should timeout.
	 *
	 * @return \DateTime
	 */
	public function retryUntil()
	{
		return now()->addSeconds(5);
	}

	/**
	 * Handles a queued import job
	 * @throws \Exception
	 */
	public function handle()
	{

		Log::info("Startando importacao ...");

		if ($this->importJob->status === ImportJob::STATUS_COMPLETED) {
			return;
		}

		try {
			set_time_limit(0);
			$this->importJob->setStatus(ImportJob::STATUS_PROCESSING);
			$this->importJob->handle();
			$this->importJob->setStatus(ImportJob::STATUS_COMPLETED);
			$this->importJob->save();
			end:
		} catch (\Exception $ex) {
			$this->importJob->setStatus(ImportJob::STATUS_FAILED);
			$this->importJob->storeError($ex);
			$this->importJob->save();
			throw $ex;
		}

		Log::info("Importacao finalizada...");
	}
}
