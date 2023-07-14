<?php

namespace BuscaAtivaEscolar\Jobs;

use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class DeleteUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit(0);

        ChildCase::whereHas('currentStep', function ($query) {
            $query->where('assigned_user_id', '=', $this->user->id);
        })->chunk(50, function ($cases) {
            Log::info("caso...");
            // foreach ($cases as $case) {
            //     $case->assigned_user_id = null;
            //     $case->currentStep->detachUser();
            //     $case->child->save(); //reindex
            // }
        });

        end:
    }
}
