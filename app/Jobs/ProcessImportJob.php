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

class ProcessImportJob implements ShouldQueue {

	use InteractsWithQueue, Queueable, SerializesModels;

	public $importJob;

	public function __construct(ImportJob $importJob) {
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
	public function handle() {

		if($this->importJob->status === ImportJob::STATUS_COMPLETED) {
			Log::info("ImportJob({$this->importJob->id}) - already completed, skipping!");
			return;
		}

		try {

			Log::info("ImportJob({$this->importJob->id}) - begin processing");

			$this->importJob->setStatus(ImportJob::STATUS_PROCESSING);

			$this->importJob->handle();

			$this->importJob->setStatus(ImportJob::STATUS_COMPLETED);

			Log::info("ImportJob({$this->importJob->id}) - completed processing");

            $this->importJob->save();

		} catch (\Exception $ex) {

			Log::error("ImportJob({$this->importJob->id}) - failed: " . $ex->getMessage());

			$this->importJob->setStatus(ImportJob::STATUS_FAILED);

			$this->importJob->storeError($ex);

			$this->importJob->save();

			throw $ex;

		}
	}

}