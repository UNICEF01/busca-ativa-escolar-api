<?php

namespace BuscaAtivaEscolar\Jobs;

use BuscaAtivaEscolar\EmailJob;
use Log;

class ProcessEmailJob
{
    public $emailJob;

    /**
     * ProcessEmailJob constructor.
     * @param EmailJob $job
     */
    public function __construct(EmailJob $emailJob)
    {
        $this->emailJob = $emailJob;
    }

    /**
     * Handles a queued email job
     * @throws \Exception
     */
    public function handle()
    {

        try {
            $this->emailJob->handle();
        } catch (\Exception $ex) {
            $this->emailJob->setStatus(EmailJob::STATUS_FAILED);
            $this->emailJob->saveError($ex);
            $this->emailJob->save();
            throw $ex;
        }
    }
}
