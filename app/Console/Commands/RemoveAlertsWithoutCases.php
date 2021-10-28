<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Child;
use Illuminate\Console\Command;
use Matrix\Exception;

class RemoveAlertsWithoutCases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:remove_alerts_without_cases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $children = [];
        foreach ($children as $child) {
           $ch = Child::where('id', '=', $child)->get()->first();
           if ($ch) {
               $this->comment($ch->name);
               try {
                   $ch->forceDelete();
               } catch (\Exception $exception) {
                   $this->comment("erro do elastic");
               }
           }
        }
    }
}
