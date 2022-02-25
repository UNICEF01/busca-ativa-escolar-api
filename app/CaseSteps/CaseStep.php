<?php
/**
 * busca-ativa-escolar-api
 * CaseStep.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 29/12/2016, 13:22
 */

namespace BuscaAtivaEscolar\CaseSteps;

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\Events\CaseStepAssigned;
use BuscaAtivaEscolar\Events\CaseStepCompleted;
use BuscaAtivaEscolar\Events\CaseStepStarted;
use BuscaAtivaEscolar\Events\CaseStepUpdated;
use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use BuscaAtivaEscolar\Traits\Data\TenantScopedModel;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 *
 * @property string $tenant_id
 * @property string $child_id
 * @property string $case_id
 *
 * @property string $step_type
 * @property string $next_type
 *
 * @property integer $index
 * @property integer $step_index
 * @property integer $next_index
 *
 * @property string $assigned_user_id
 * @property string $assigned_group_id
 *
 * @property boolean $is_pending_assignment
 * @property boolean $is_completed
 *
 * @property Carbon $completed_at
 * @property Carbon $started_at
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property Tenant $tenant
 * @property Child $child
 * @property ChildCase $child_case
 * @property User|null $assigned_user
 * @property Group|null $assigned_group
 *
 * @property array|null $_next
 */
abstract class CaseStep extends Model {

	use SoftDeletes;
	use IndexedByUUID;
	use TenantScopedModel;

	const SLUGS = [
		'alerta',
		'pesquisa',
		'analise_tecnica',
		'gestao_do_caso',
		'rematricula',
		'1a_observacao',
		'2a_observacao',
		'3a_observacao',
		'4a_observacao',
	];

	/**
	 * Base array of fillable fields, shared between all case steps.
	 * @var array
	 */
	protected $baseFillable = [
		'tenant_id',
		'child_id',
		'case_id',
		'step_type',
		'step_index',
		'next_index',
		'next_type',
		'assigned_user_id',
		'assigned_group_id',
		'is_pending_assignment',
		'completed_at',
		'started_at',
		'is_completed',
	];

	protected $dates = [
		'completed_at',
		'started_at',
	];

	protected $casts = [
		'is_completed' => 'boolean',
		'is_pending_assignment' => 'boolean',
		'index' => 'integer',
		'next_index' => 'integer',
	];

	/**
	 * List of fillable fields for each step; this is extended by each step class.
	 * @var array
	 */
	public $stepFields = [];

	/**
	 * Internal: The cached next case step in the sequence
	 * @var CaseStep
	 */
	protected $_next = null;

	// ------------------------------------------------------------------------

	/**
	 * CaseStep constructor.
	 * Generates the "fillable" model property by combining shared and specific fields.
	 *
	 * @param array $attributes
	 */
	public function __construct(array $attributes = []) {
		$this->fillable = array_merge($this->baseFillable, $this->stepFields);
		$this->incrementing = false; // This is already defined by IndexedByUUID, but we're overriding __construct here

		parent::__construct($attributes);
	}

	// ------------------------------------------------------------------------

	/**
	 * The tenant this step belongs to.
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 * @var $tenant \BuscaAtivaEscolar\Tenant
	 */
	public function tenant() {
		return $this->hasOne('BuscaAtivaEscolar\Tenant', 'id', 'tenant_id');
	}

	/**
	 * The child this step belongs to.
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 * @var $child \BuscaAtivaEscolar\Child
	 */
	public function child() {
		return $this->hasOne('BuscaAtivaEscolar\Child', 'id', 'child_id');
	}

	/**
	 * The case this step belongs to.
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 * * @var $childCase \BuscaAtivaEscolar\ChildCase
	 */
	public function childCase() {
		return $this->hasOne('BuscaAtivaEscolar\ChildCase', 'id', 'case_id');
	}

	/**
	 * The user currently assigned to this step. (null if step is unassigned)
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 * @var $assignedUser \BuscaAtivaEscolar\User
	 */
	public function assignedUser() {
		return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'assigned_user_id');
	}

	/**
	 * The group currently assigned to this step.
	 * When no user is assigned, this group indicates which group supervisor should (re)assign.
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 * @var $assignedGroup \BuscaAtivaEscolar\Group
	 */
	public function assignedGroup() {
		return $this->hasOne('BuscaAtivaEscolar\Group', 'id', 'assigned_group_id');
	}

	/**
	 * Fetches the next step in the sequence, or null if no step is next.
	 * @return CaseStep|null
	 */
	public function fetchNextStep() {
		if(!$this->next_type || !$this->next_index) return null;

		if($this->_next === null) {
			$this->_next = self::fetchWithinCase($this->case_id, $this->next_type, $this->next_index);
		}

		return $this->_next;
	}

	/**
	 * Starts a step, clearing the completion flag and firing appropriate events.
	 * This will call onStart(), which is responsible for determining if a step begins by waiting for assignment.
	 *
	 * @param CaseStep|null $prevStep The step that preceded this one, or null if none
	 */
	public function start($prevStep) {
		$this->is_completed = false;
		$this->started_at = date('Y-m-d H:i:s');
		$this->save();

		$this->onStart($prevStep);

		// Update search index
		$this->child->reindex();

		event(new CaseStepStarted($this, $prevStep));
	}

	/**
	 * Completes a step, marking it as completed and advancing the case to the next step.
	 * @return CaseStep|null Returns the next step, or null if none
	 */
	public function complete() {

		$this->is_completed = true;
		$this->completed_at = date('Y-m-d H:i:s');
		$this->save();

		$shouldContinue = $this->onComplete();

		if($shouldContinue) {
			$nextStep = $this->childCase->advanceToNextStep($this);
		}

		// Update search index
		$this->child->reindex();

		event(new CaseStepCompleted($this, $nextStep ?? null));

		return ($nextStep ?? null);
	}

	/**
	 * Flags the step as currently pending assignment.
	 * This will make the step show up in the list of "assignments pending".
	 */
	public function flagAsPendingAssignment() {
		$this->is_pending_assignment = true;
		$this->save();
	}

	/**
	 * Assigns this step to a specific user.
	 * This will clear the "is pending assignment" flag.
	 *
	 * @param User $user
	 */
	public function assignToUser(User $user) {

		$this->is_pending_assignment = false;
		$this->assigned_user_id = $user->id;
		$this->save();

		$this->onAssign($user);

		$this->child->reindex();

		event(new CaseStepAssigned($this, $user));
	}

    /**
     * Detach the user of step.
     */
    public function detachUser() {
        $this->is_pending_assignment = true;
        $this->assigned_user_id = null;
        $this->save();
    }

	/**
	 * Updates the step fields, ignoring fields not in stepFields.
	 * Emits the "updated" event.
	 *
	 * @param array $data The fields that should be updated
	 * @return array $input The actual updated fields
	 */
	public function setFields(array $data) {
		$input = collect($data)->only($this->stepFields)->toArray();
		$this->fill($input);
		$this->save();

		$this->onUpdated();

		// Update search index
		$this->child->reindex();

		event(new CaseStepUpdated($this, $data));

		return $input;
	}

	/**
	 * Gets an associative array with all step fields in this step.
	 * Skips step metadata.
	 *
	 * @return array
	 */
	public function getFields() {
		return collect($this->toArray())->only($this->stepFields)->toArray();
	}

	/**
	 * Gets the human-readable name for the step
	 * @return string|\Symfony\Component\Translation\TranslatorInterface
	 */
	public function getName() {
		return trans('case_step.name.' . $this->step_type, ['report_index' => ($this->report_index ?? '')]);
	}

	/**
	 * Gets the slug that identifies this step
	 * @return string
	 */
	public function getSlug() {
		return str_slug($this->getName(), '_');
	}

	/**
	 * Checks if the step is late or not
	 * @param Carbon $now
	 * @param int $maxDays
	 * @return boolean
	 */
	public function isLate($now = null, $maxDays = 0) {
		if(!$now) $now = Carbon::now();
		if(!$this->started_at) return true;

        //if step case is in index 60, 70, 80 or 90, consider max days + 10 to set late
        if($this->step_index == 60 OR $this->step_index == 70 OR $this->step_index == 80 OR $this->step_index == 90){
            $maxDays = $maxDays+10;
        }

		return ($now->diffInDays($this->started_at) >= $maxDays);
	}

	// ------------------------------------------------------------------------
	// These are not abstract because they're optional

	protected function onStart($prevStep = null) {}
	protected function onComplete() : bool { return true; }
	protected function onUpdated() {}
	protected function onAssign(User $user) {}

	// ------------------------------------------------------------------------
	// These are meant to be overriden as necessary

	/**
	 * Can the case be interrupted (@see ChildCase::interrupt()) at this step?
	 * @return bool
	 */
	public function canInterrupt() {
		return false;
	}

	/**
	 * Gets a list of "column => value" filters for the list of assignable users.
	 * Defaults to any user (within tenant scope)
	 * @param $query Builder A query builder that will be used to filter out the users.
	 * @return Builder The query builder after applying the relevant fields.
	 */
	public function applyAssignableUsersFilter(Builder $query) {
		return $query;
	}

	/**
	 * Returns a validator that validates adequately the step fields.
	 * @param array $data The input data (usually Request::all())
	 * @param boolean $isCompletingStep Is the validation being done at step completion?
	 * @return \Illuminate\Contracts\Validation\Validator The Validator instance
	 */
	public function validate($data, $isCompletingStep = false) {
		return validator($data, []);
	}

	// ------------------------------------------------------------------------

	/**
	 * Finds a Child case step by it's type class name and ID
	 *
	 * @param string $step_type The fully-qualified (w/ namespace) step class name
	 * @param string $step_id The step ID
	 * @return CaseStep
	 */
	public static function fetch($step_type, $step_id) {
		return ($step_type)::findOrFail($step_id);
	}

	/**
	 * Finds a Child case step by it's case and internal index
	 *
	 * @param string $case_id The child case ID
	 * @param string $step_type The type of step
	 * @param integer $index The case step sequence index
	 * @return CaseStep|static
	 */
	public static function fetchWithinCase($case_id, $step_type, $index) {
		return ($step_type)::query()
			->where('case_id', $case_id)
			->where('step_index', $index)
			->firstOrFail();
	}

	/**
	 * Spawns a case step by it's class, pre-filling it with data and attaching it to a case.
	 * Does not modify the parent ChildCase instance.
	 *
	 * @param ChildCase $case The case this step belongs to.
	 * @param int $index The ordering index
	 * @param string $class The step class name (without namespace) to use. Must inherit this class (CaseStep).
	 * @param array $next The data for the next step (should contain keys "index" and "class")
	 * @param array $data The data to fill this step with.
	 * @return CaseStep
	 */
	public static function spawn(ChildCase $case, int $index, string $class, array $next, array $data) {
		$data['tenant_id'] = $case->tenant_id;
		$data['case_id'] = $case->id;
		$data['child_id'] = $case->child_id;

		$data['step_index'] = $index;
		$data['next_index'] = $next['index'] ?? null;
		$data['next_type'] = isset($next['class']) ? "BuscaAtivaEscolar\\CaseSteps\\" . $next['class'] : null;

		$data['step_type'] = "BuscaAtivaEscolar\\CaseSteps\\{$class}";

		$data['is_completed'] = false;

		return ($data['step_type'])::create($data);
	}

}