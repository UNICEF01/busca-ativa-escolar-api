<?php

namespace BuscaAtivaEscolar\Jobs;

use BuscaAtivaEscolar\CaseSteps\AnaliseTecnica;
use BuscaAtivaEscolar\CaseSteps\GestaoDoCaso;
use BuscaAtivaEscolar\CaseSteps\Observacao;
use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\CaseSteps\Rematricula;
use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\User;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        $this->deleteUser();
    }

    protected function deleteUser()
    {

        $cases = ChildCase::where('case_status', ChildCase::STATUS_IN_PROGRESS)
            ->whereHasMorph(
                'currentStep',
                [Pesquisa::class, AnaliseTecnica::class, GestaoDoCaso::class, Observacao::class, Rematricula::class],
                function (Builder $query) {
                    $query->where('assigned_user_id', '=', $this->user->id);
                }
            )->select('currentStep', 'assigned_user_id')->skip(0)->take(20)->get();

        if ($cases->count() == 0) {
            return true;
        }

        DB::beginTransaction();
        foreach ($cases as $case) {
            try {
                $case->currentStep->detachUser();
                $case->currentStep->save();
                $case->assigned_user_id = null;
                $case->save();
                $case->child->save();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        }
        DB::commit();

        return $this->deleteUser();
    }
}
