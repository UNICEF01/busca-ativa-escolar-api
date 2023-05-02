<?php
/**
 * busca-ativa-escolar-api
 * ChildCase.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 28/12/2016, 22:00
 */

namespace BuscaAtivaEscolar;

use BuscaAtivaEscolar\CaseSteps\CaseStep;
use BuscaAtivaEscolar\AlertCase;
use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\Events\ChildCaseCancelled;
use BuscaAtivaEscolar\Events\ChildCaseClosed;
use BuscaAtivaEscolar\Events\ChildCaseCompleted;
use BuscaAtivaEscolar\Events\ChildCaseInterrupted;
use BuscaAtivaEscolar\Http\Controllers\Resources\AlertsController;
use BuscaAtivaEscolar\Mail\ReopenCaseNotification;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use BuscaAtivaEscolar\Traits\Data\TenantScopedModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

/**
 * @property int $id
 *
 * @property string $tenant_id
 * @property string $child_id
 * @property string $case_status
 * @property string $cancel_reason
 * @property string $name
 * @property string $risk_level
 * @property boolean $is_current
 * @property string $assigned_group_id
 * @property string $assigned_user_id
 * @property string $alert_cause_id
 * @property array $case_cause_ids
 * @property array $place_city
 * @property string $created_by_user_id
 * @property string $current_step_id
 * @property string $current_step_type
 * @property Collection|array $linked_steps
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property mixed $tenant
 * @property mixed $child
 * @property mixed $alert
 * @property mixed $assigned_user
 * @property mixed $current_step
 *
 * @property array|null $_steps
 */
class ChildCase extends Model
{

    use SoftDeletes;
    use IndexedByUUID;
    use TenantScopedModel;

    // Case statuses
    const STATUS_CANCELLED = "cancelled";
    const STATUS_IN_PROGRESS = "in_progress";
    const STATUS_INTERRUPTED = "interrupted";
    const STATUS_COMPLETED = "completed";
    const STATUS_TRANSFERRED = "transferred";

    // Case risk levels
    const RISK_LEVEL_HIGH = "high";
    const RISK_LEVEL_MEDIUM = "medium";
    const RISK_LEVEL_LOW = "low";

    // Case cancel reasons
    const CANCEL_REASON_DUPLICATE = "duplicate";
    const CANCEL_REASON_DEATH = "death";
    const CANCEL_REASON_NOT_FOUND = "not_found";
    const CANCEL_REASON_WRONGFUL_INSERTION = "wrongful_insertion";
    const CANCEL_REASON_REJECTED_ALERT = "rejected_alert";
    const CANCEL_REASON_CITY_TRANSFER = "city_transfer";
    
    //only for control of UNICEF ()
    const CANCEL_REASON_JUSTIFIED_CANCELLED = "justified_cancelled";

    const CANCEL_REASONS = [
        self::CANCEL_REASON_DUPLICATE,
        self::CANCEL_REASON_DEATH,
        self::CANCEL_REASON_NOT_FOUND,
        self::CANCEL_REASON_WRONGFUL_INSERTION,
        self::CANCEL_REASON_CITY_TRANSFER,
        self::CANCEL_REASON_JUSTIFIED_CANCELLED
    ];

    /**
     * The list and order of the default steps a case has.
     *
     * Warning: do NOT rely on lookups on this table for info; the structure/order may change as the application evolves
     * Use instead the case "linked_steps" array and metadata on each step row
     *
     * @var array
     */
    public static $REQUIRED_STEPS = [
        10 => ['index' => 10, 'class' => 'Alerta', 'next' => 20, 'fill' => ['is_completed' => 1]],
        20 => ['index' => 20, 'class' => 'Pesquisa', 'next' => 30],
        30 => ['index' => 30, 'class' => 'AnaliseTecnica', 'next' => 40],
        40 => ['index' => 40, 'class' => 'GestaoDoCaso', 'next' => 50],
        50 => ['index' => 50, 'class' => 'Rematricula', 'next' => 60],
        60 => ['index' => 60, 'class' => 'Observacao', 'next' => 70, 'fill' => ['report_index' => 1]],
        70 => ['index' => 70, 'class' => 'Observacao', 'next' => 80, 'fill' => ['report_index' => 2]],
        80 => ['index' => 80, 'class' => 'Observacao', 'next' => 90, 'fill' => ['report_index' => 3]],
        90 => ['index' => 90, 'class' => 'Observacao', 'next' => null, 'fill' => ['report_index' => 4]],
    ];

    protected $table = "children_cases";
    protected $fillable = [
        'tenant_id',
        'child_id',

        'case_status',
        'cancel_reason',

        'name',

        'risk_level',

        'is_current',

        'assigned_group_id',
        'assigned_user_id',

        'alert_cause_id',
        'case_cause_ids',

        'created_by_user_id',

        'current_step_id',
        'current_step_type',

        'linked_steps',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'linked_steps' => 'collection',
        'case_cause_ids' => 'array',
    ];

    /**
     * Internal cache of steps filled by fetchSteps()
     * @var CaseStep[]
     */
    protected $_steps = null;

    /**
     * The tenant this case belongs to.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tenant()
    {
        return $this->hasOne('BuscaAtivaEscolar\Tenant', 'id', 'tenant_id');
    }

    /**
     * The child this case belongs to.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function child()
    {
        return $this->hasOne('BuscaAtivaEscolar\Child', 'id', 'child_id');
    }

    /**
     * The user that is assigned as responsible for this case.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function assignedUser()
    {
        return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'assigned_user_id');
    }

    /**
     * The current step this case is at.
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function currentStep()
    {
        return $this->morphTo();
    }

    // ------------------------------------------------------------------------

    /**
     * Fetches (as model instances) all steps associated with this case.
     * This method uses the linked_steps JSON to resolve step type and IDs.
     *
     * @return CaseStep[]
     */

    public function fetchSteps()
    {
        if ($this->_steps != null) return $this->_steps;

        $this->_steps = [];

        foreach ($this->linked_steps as $i => $step) {
            $step = ($step['type'])::find($step['id']);
            $step->order = $i;
            array_push($this->_steps, $step);
        }

        return $this->_steps;
    }

    /**
     * Advances the case current step pointer to the next step in the sequence, effectively starting it.
     * This does NOT complete the current step, and is actually called by the "complete()" method in CaseStep.
     * This is to allow for future non-linear sequence progressions (multiple uncompleted steps).
     *
     * @param CaseStep|null $current The case step to consider as current; defaults to the one marked in current_step_id
     * @return CaseStep|null The next step in the sequence, or null if none
     */
    public function advanceToNextStep($current = null)
    {
        if (!$current) $current = $this->currentStep;
        $next = $current->fetchNextStep();
        /* @var $next CaseStep */

        if (!$next) return null;

        // Assigned user is resolved here when possible, via overridden "CaseStep::onStart"
        $next->start($current);


        // Update case pointers
        $this->current_step_type = $next->step_type;
        $this->current_step_id = $next->id;

        if ($next->assigned_user_id) $this->assigned_user_id = $next->assigned_user_id;

        $this->save();


        // Update child pointers
        $this->child->current_step_type = $next->step_type;
        $this->child->current_step_id = $next->id;

        $this->child->save();

        return $next;

    }

    /**
     * Completes the current case, signalling the children has been reinserted in school.
     * This sets the child status to In School. Emits the "completed" and "closed" events.
     *
     * @return bool Should the case continue?
     */
    public function complete()
    {
        $this->case_status = self::STATUS_COMPLETED;
        $this->save();

        $this->child->setStatus(Child::STATUS_IN_SCHOOL);

        event(new ChildCaseCompleted($this->child, $this));
        event(new ChildCaseClosed($this->child, $this));

        return false;
    }

    /**
     * Cancels the current case due to a specific reason (duplicate, invalid, etc).
     * This sets the child status to Cancelled. Will emit the "cancelled" and "closed" events.
     *
     * @param string $reason The reason for cancellation.
     *
     * @return bool Should the case continue?
     */
    public function cancel($reason = "")
    {
        $this->case_status = self::STATUS_CANCELLED;
        $this->cancel_reason = $reason;
        $this->save();

        $this->child->setStatus(Child::STATUS_CANCELLED);

        event(new ChildCaseCancelled($this->child, $this, $reason));
        event(new ChildCaseClosed($this->child, $this));

        return false;
    }

    public function reopen($reason = "")
    {

        if( $this->case_status == ChildCase::STATUS_INTERRUPTED OR $this->case_status == ChildCase::STATUS_TRANSFERRED){
            return response()->json(
                [
                    'result' => 'O caso selecionado já está interrompido',
                    'status' => 'error'
                ]
            );
        }

        $this->case_status = self::STATUS_INTERRUPTED;

        $this->interrupt_reason = $reason;

        $this->save();

        $this->child->setStatus(Child::STATUS_INTERRUPTED);

        $child = $this->child->getAttributes();

        $pesquisaArray = $this->returnPesquisaArray( $this->child->pesquisa->replicate()->toArray() );

        $currentUser = \Auth::user();

        $data = $this->returnDataFromChild($child);

        $objChild = Child::spawnFromAlertData($currentUser->tenant, $currentUser->id, $data);

        $newChildObj = Child::where('id', $objChild->id)->first();

        $newChildObj->father_id = $child['id'];

        $newChildObj->acceptAlert(['id'=> $objChild->id]);

        $pesquisaNewChildObj = Pesquisa::where('child_id', $newChildObj->id)->first();

        $pesquisaNewChildObj->fill($pesquisaArray);

        $pesquisaNewChildObj->save();

        /* @var $reopeningRequest ReopeningRequests */
        $reopeningRequest = ReopeningRequests::where(
            [
                'child_id' => $this->child->id,
                'status' => ReopeningRequests::STATUS_REQUESTED,
                'type_request' => ReopeningRequests::TYPE_REQUEST_REOPEN
            ]
        )->first();

        if ($reopeningRequest != null){

            $reopeningRequest->status = ReopeningRequests::STATUS_APPROVED;
            $reopeningRequest->save();

            if( $reopeningRequest->requester != null) {

                try{

                        $msg = new ReopenCaseNotification(
                        $this->child->id,
                        $this->child->name,
                        $this->id,
                        null,
                        $reopeningRequest->requester->name,
                        \Auth::user()->name,
                        $reopeningRequest->id,
                        null,
                        null,
                        ReopenCaseNotification::TYPE_ACCEPT_REOPEN
                    );

                    Mail::to( $reopeningRequest->requester->email )->send($msg);

                }catch (\Exception $e){

                }
            }

        }

        event(new ChildCaseCancelled($this->child, $this, $reason));
        event(new ChildCaseClosed($this->child, $this));

        return response()->json(
            [
                'status' => 'success',
                'child_id'=> $newChildObj->id,
                'result' => 'Reabertura realizada com sucesso'
            ]
        );
    }

    public function requestReopen($reason = "")
    {

        if( $this->case_status == ChildCase::STATUS_INTERRUPTED OR $this->case_status == ChildCase::STATUS_TRANSFERRED){
            return response()->json(
                [
                    'result' => 'O caso selecionado já foi reaberto',
                    'status' => 'error'
                ]
            );
        }

        /* @var $tenant Tenant */
        $tenant = \Auth::user()->tenant;

        /* @var $requesterUser User */
        $requesterUser = \Auth::user();

        /* @var $reopeningRequest ReopeningRequests */
        $reopeningRequest = ReopeningRequests::where(
            [
                'child_id' => $this->child->id,
                'status' => ReopeningRequests::STATUS_REQUESTED,
                'type_request' => ReopeningRequests::TYPE_REQUEST_REOPEN
            ]
        )->first();

        if( $reopeningRequest != null ){

            $now = Carbon::now();

            if( $now->diffInDays( $reopeningRequest->updated_at ) <= 15 ){

                return response()->json(
                    [
                        'result' => 'O usuário '.$reopeningRequest->requester->name.' já solicitou a reabertura deste caso a menos de 15 dias',
                        'status' => 'error'
                    ]
                );

            }else{

                $dataReopeningRequest = [
                    'requester_id' => $requesterUser->id,
                    'interrupt_reason' => $reason
                ];

                $reopeningRequest->fill($dataReopeningRequest);

                $reopeningRequest->save();

            }

        }

        if( $reopeningRequest == null ){

            $dataReopeningRequest = [
                'requester_id' => $requesterUser->id,
                'recipient_id' => null,
                'child_id' => $this->child->id,
                'status' => ReopeningRequests::STATUS_REQUESTED,
                'interrupt_reason' => $reason,
                'type_request' => ReopeningRequests::TYPE_REQUEST_REOPEN,
                'tenant_requester_id' => $tenant->id,
                'tenant_recipient_id' => $tenant->id
            ];

            $reopeningRequest = ReopeningRequests::create($dataReopeningRequest);

        }

        /* @var $coordinators Collection */
        $coordinators = User::where(
            [
                'tenant_id' => $tenant->id,
                'type' => User::TYPE_GESTOR_OPERACIONAL
            ]
        )->get();

        if ( is_array($coordinators) && (sizeof($coordinators) <= 0) ) { // PHP 7.2
//        if ( $coordinators->count() <= 0 ) { // Implementação antiga PHP 7.1
            return response()->json(
                [
                    'result' => 'Solicitação realizada com sucesso, porém não existem coordenadores ativos no município',
                    'status' => 'success'
                ]
            );
        }

        try{

            foreach ( $coordinators as $coordinator ) {

                $msg = new ReopenCaseNotification(
                    $this->child->id,
                    $this->child->name,
                    $this->id,
                    $reason,
                    $coordinator->name,
                    \Auth::user()->name,
                    $reopeningRequest->id,
                    null,
                    null,
                    ReopenCaseNotification::TYPE_REOPEN
                );

                Mail::to($coordinator->email)->send($msg);

            }

        } catch (\Exception $exception){

            return response()->json(
                [
                    'result' => 'Solicitação realizada com sucesso. Já está disponível para o outro município',
                    'status' => 'success'
                ]
            );

        }

        return response()->json(
            [
                'result' => 'Requisição realizada com sucesso',
                'status' => 'success'
            ]
        );

    }

    public function transfer(){

        if( $this->case_status == ChildCase::STATUS_INTERRUPTED OR $this->case_status == ChildCase::STATUS_TRANSFERRED){
            return response()->json(
                [
                    'result' => 'O caso selecionado já está interrompido',
                    'status' => 'error'
                ]
            );
        }

        /* @var $reopeningRequest ReopeningRequests */
        $reopeningRequest = ReopeningRequests::where(
            [
                'child_id' => $this->child->id,
                'status' => ReopeningRequests::STATUS_REQUESTED,
                'type_request' => ReopeningRequests::TYPE_REQUEST_TRANSFER
            ]
        )->first();

        if( is_array($reopeningRequest) && (sizeof($reopeningRequest) <= 0)){
//        if( $reopeningRequest->count() <= 0 ){
            return response()->json(
                [
                    'result' => 'Não existe uma solicitação de transferência para o caso informado',
                    'status' => 'error'
                ]
            );
        }

        $this->case_status = self::STATUS_TRANSFERRED;

        $this->interrupt_reason = $reopeningRequest->interrupt_reason;

        $this->save();

        $this->child->setStatus(Child::STATUS_TRANSFERRED);

        $child = $this->child->getAttributes();

        $pesquisaArray = $this->returnPesquisaArray( $this->child->pesquisa->replicate()->toArray() );

        $data = $this->returnDataFromChild($child);

        $objChild = Child::spawnFromAlertData(\Auth::user()->tenant, \Auth::user()->id, $data);

        $newChildObj = Child::where('id', $objChild->id)->first();

        $newChildObj->father_id = $child['id'];

        $newChildObj->acceptAlert(['id'=> $objChild->id]);

        $pesquisaNewChildObj = Pesquisa::where('child_id', $newChildObj->id)->first();

        $pesquisaNewChildObj->fill($pesquisaArray);

        $pesquisaNewChildObj->save();

        if( $reopeningRequest->requester != null) {

            try{

                $msg = new ReopenCaseNotification(
                    $this->child->id,
                    $this->child->name,
                    $this->id,
                    null,
                    $reopeningRequest->requester->name,
                    \Auth::user()->name,
                    $reopeningRequest->id,
                    $reopeningRequest->tenantRequester,
                    $reopeningRequest->tenantRecipient,
                    ReopenCaseNotification::TYPE_ACCEPT_TRANSFER
                );

                Mail::to( $reopeningRequest->requester->email )->send($msg);

            } catch (\Exception $exception){
                //TODO em caso de erro de envio de email?
            }
        }

        $reopeningRequest->status = ReopeningRequests::STATUS_APPROVED;

        $reopeningRequest->save();

        event(new ChildCaseCancelled($this->child, $this, $reopeningRequest->interrupt_reason));
        event(new ChildCaseClosed($this->child, $this));

        return response()->json(
            [
                'status' => 'success',
                'child_id'=> $newChildObj->id,
                'result' => 'Transferência realizada com sucesso'
            ]
        );

    }

    public function requestTransfer($reason = "", $case_id, $tenant_recipient_id, $city_id){

        if( $this->case_status == ChildCase::STATUS_INTERRUPTED ){
            return response()->json(
                [
                    'result' => 'O caso selecionado já está interrompido',
                    'status' => 'error'
                ]
            );
        }

        /* @var $tenant Tenant */
        $tenant_recipient = Tenant::where('id', $tenant_recipient_id)->first();

        /* @var $tenant Tenant */
        $tenant = \Auth::user()->tenant;

        /* @var $requesterUser User */
        $requesterUser = \Auth::user();

        /* @var $reopeningRequest ReopeningRequests */
        $reopeningRequest = ReopeningRequests::where(
            [
                'child_id' => $this->child->id,
                'status' => ReopeningRequests::STATUS_REQUESTED,
                'type_request' => ReopeningRequests::TYPE_REQUEST_TRANSFER
            ]
        )->first();

        if( $reopeningRequest != null ){

            $now = Carbon::now();

            if( $now->diffInDays( $reopeningRequest->updated_at ) <= 15 ){

                return response()->json(
                    [
                        'result' => 'O usuário '.$reopeningRequest->requester->name.' já solicitou a transferência deste caso a menos de 15 dias',
                        'status' => 'error'
                    ]
                );

            }else{

                $dataReopeningRequest = [
                    'requester_id' => $requesterUser->id,
                    'interrupt_reason' => $reason
                ];

                $reopeningRequest->fill($dataReopeningRequest);

                $reopeningRequest->save();

            }

        }

        if( $reopeningRequest == null ){

            $dataReopeningRequest = [
                'requester_id' => $requesterUser->id,
                'recipient_id' => null,
                'child_id' => $this->child->id,
                'status' => ReopeningRequests::STATUS_REQUESTED,
                'interrupt_reason' => $reason,
                'type_request' => ReopeningRequests::TYPE_REQUEST_TRANSFER,
                'tenant_requester_id' => $tenant->id,
                'tenant_recipient_id' => $tenant_recipient_id
            ];

            $reopeningRequest = ReopeningRequests::create($dataReopeningRequest);

        }

        //Coordenadores do município a ser reportado

        /* */
        $requesterUser->type = User::TYPE_GESTOR_NACIONAL;

        /* @var $coordinators Collection */
        $coordinators = User::where([
                'tenant_id' => $tenant_recipient_id,
                'type' => User::TYPE_GESTOR_OPERACIONAL
            ]
        )->get();

        /* */
        $requesterUser->type = User::TYPE_GESTOR_OPERACIONAL;

        if (is_array($coordinators) && (sizeof($coordinators) <= 0)) {
//        if ( $coordinators->count() <= 0 ) {
            return response()->json(
                [
                    'result' => 'Solicitação realizada com sucesso, porém não existem coordendores ativos no município',
                    'status' => 'success'
                ]
            );
        }

        try{

            foreach ( $coordinators as $coordinator ) {

                $msg = new ReopenCaseNotification(
                    $this->child->id,
                    $this->child->name,
                    $this->id,
                    $reason,
                    $coordinator->name,
                    \Auth::user()->name,
                    $reopeningRequest->id,
                    $tenant,
                    $tenant_recipient,
                    ReopenCaseNotification::TYPE_TRANSFER
                );

                Mail::to($coordinator->email)->send($msg);

            }

        } catch (\Exception $exception){

            return response()->json(
                [
                    'result' => 'Solicitação realizada com sucesso. Já está disponível para o outro município' ,
                    'status' => 'success'
                ]
            );

        }

        return response()->json(
            [
                'result' => 'Solicitação realizada com sucesso. Já está disponível para o outro município',
                'status' => 'success'
            ]
        );

    }


    /**
     * Closes the case due to an interruption (i.e. another evasion by the child).
     * This sets the child status to Out of School, and emits the "interrupted" and "closed" events.
     *
     * @return bool Should the case continue?
     */
    public function interrupt()
    {
        $this->case_status = self::STATUS_INTERRUPTED;
        $this->save();

        $this->child->setStatus(Child::STATUS_OUT_OF_SCHOOL);

        event(new ChildCaseInterrupted($this->child, $this));
        event(new ChildCaseClosed($this->child, $this));

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Generates a name for a new case, based on the current year and how many cases there has been for this child.
     *
     * @param Child $child The child we're creating a new case for
     * @return string The generated name
     */
    public static function generateName(Child $child)
    {
        $numCases = self::query()->where('child_id', $child->id)->count();
        return date('Y') . '/' . (intval($numCases) + 1);
    }

    /**
     * Spawns a new Child Case, along with the default Case Steps.
     * Internally resolves risk level and responsability via Tenant workflow settings.
     * Also handles linking the spawned steps, and pre-fills the Alerta step with given data.
     *
     * @param Child $child The child this case belongs to
     * @param array $data Data to pre-fill the Alerta step (as well as internal child data, such as "name" and "age")
     * @param bool $is_current Is this case the current case (does NOT mark other cases as non-current!)
     * @return ChildCase The spawned case
     */
    public static function spawn(Child $child, array $data, bool $is_current = true)
    {

        $tenant = $child->tenant;

        $data['tenant_id'] = $tenant->id;
        $data['child_id'] = $child->id;

        $data['is_current'] = $is_current;

        $data['name'] = self::generateName($child);

        // TODO: set assigned group/user via tenant settings

        $case = parent::create($data);
        /* @var $case ChildCase */
        $steps = [];

        foreach (self::$REQUIRED_STEPS as $index => $step) { // Spawns out the default step structure
            $next = self::$REQUIRED_STEPS[$step['next']] ?? [];
            $spawnedStep = CaseStep::spawn($case, $step['index'], $step['class'], $next, $step['fill'] ?? []);

            array_push($steps, $spawnedStep);
        }

        $case->linked_steps = collect($steps)->map(function ($step, $key) { // Builds linked steps structure
            return [
                'id' => $step->id,
                'type' => $step->step_type,
                'index' => $step->step_index,
                'next' => ['type' => $step->next_type, 'index' => $step->next_index]
            ];
        });

        // Sets the first step as the current step (Alerta)
        $current_step = array_shift($steps);
        $case->current_step_id = $current_step->id;
        $case->current_step_type = $current_step->step_type;

        $case->save();

        return $case;
    }

    private function returnDataFromChild($child)
    {
        $alert = \BuscaAtivaEscolar\AlertCase::where('child_id', $child['id'])->first();
        $placeCity = \BuscaAtivaEscolar\City::where('id', $alert['place_city_id'])->first();

        $alertData = $alert->getAttributes();

        $alertData['place_city'] = $placeCity->getAttributes();

        $data = new \stdClass();
        $data->name = $alertData['name'];
        $data->place_address = $alertData['place_address'];
        $data->place_uf = $alertData['place_uf'];
        $data->place_city = $placeCity->getAttributes();
        $data->alert_cause_id = $alertData['alert_cause_id'];
        $data->dob = $alertData['dob'];
        $data->mother_name = $alertData['mother_name'];
        $data->place_negighborhood = $alertData['place_neighborhood'];
        $data->place_city_id = $alertData['place_city_id'];
        $data->place_city_name = $alertData['place_city_name'];
        $data = (array)$data;

        return $data;
    }

    private function returnPesquisaArray($toArray)
    {

        /*
         * remove elementos do array pesquisa e mantém
         * essenciais para atualizacao do novo
         */
        unset(
            $toArray['id'],
            $toArray['tenant_id'],
            $toArray['child_id'],
            $toArray['case_id'],
            $toArray['is_completed'],
            $toArray['assigned_user_id'],
            $toArray['assigned_group_id'],
            $toArray['is_pending_assignment'],
            $toArray['completed_at'],
            $toArray['deleted_at']
        );

        return $toArray;
    }

}