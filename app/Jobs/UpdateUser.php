<?php

namespace BuscaAtivaEscolar\Jobs;

use BuscaAtivaEscolar\CaseSteps\AnaliseTecnica;
use BuscaAtivaEscolar\CaseSteps\GestaoDoCaso;
use BuscaAtivaEscolar\CaseSteps\Observacao;
use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\CaseSteps\Rematricula;
use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\User;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $newGroup;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $newGroupId)
    {
        $this->user = $user;
        $this->newGroup = Group::where('id', $newGroupId)->get()->first();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->updateUser();
    }

    protected function updateUser()
    {
        $count = ChildCase::where('case_status', ChildCase::STATUS_IN_PROGRESS)
            ->whereHasMorph(
                'currentStep',
                [Pesquisa::class, AnaliseTecnica::class, GestaoDoCaso::class, Observacao::class, Rematricula::class],
                function (Builder $query) {
                    $query->where('assigned_user_id', '=', $this->user->id);
                }
            )->count();

        for ($i = 0; $i < $count; $i++) {

            $case = ChildCase::where('case_status', ChildCase::STATUS_IN_PROGRESS)
                ->whereHasMorph(
                    'currentStep',
                    [Pesquisa::class, AnaliseTecnica::class, GestaoDoCaso::class, Observacao::class, Rematricula::class],
                    function (Builder $query) {
                        $query->where('assigned_user_id', '=', $this->user->id);
                    }
                )->get()->first();

            DB::beginTransaction();
            try {
                $parentsIdOfCase = $case->group->getArrayOfParentsId();
                if (!in_array($this->newGroup->id, $parentsIdOfCase) and $this->newGroup->id != $case->group->id) {
                    $case->currentStep->detachUser();
                    $case->currentStep->save();
                    $case->assigned_user_id = null;
                    $case->save();
                    $case->child->save();
                } else {
                    $case->child->save();
                }
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            DB::commit();
        }
    }
}
