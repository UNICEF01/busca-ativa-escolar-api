<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;

class UpdateGoalsCities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:update_goals_cities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza as metas dos municípios baseada na data atual e delta registrado no banco';

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
     * @return int
     */
    public function handle()
    {

    }
}
