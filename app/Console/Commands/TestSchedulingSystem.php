<?php
/**
 * busca-ativa-escolar-api
 * TestSchedulingSystem.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 13/02/2017, 11:59
 */

namespace BuscaAtivaEscolar\Console\Commands;


use Log;

class TestSchedulingSystem extends Command {

	protected $signature = 'debug:test_scheduling_system';
	protected $description = 'Prints a test message in the log; used to test if cron scheduling is working';

	public function handle() {
		Log::notice("Scheduling system is working!");
	}

}