<?php

/**
 * busca-ativa-escolar-api
 * Child.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/12/2016, 21:20
 */

namespace BuscaAtivaEscolar;

use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\CaseSteps\CaseStep;
use BuscaAtivaEscolar\Data\AlertCause;
use BuscaAtivaEscolar\Data\CaseCause;
use BuscaAtivaEscolar\Events\AlertAccepted;
use BuscaAtivaEscolar\Events\AlertRejected;
use BuscaAtivaEscolar\Events\AlertSpawned;
use BuscaAtivaEscolar\Events\AlertStatusChanged;
use BuscaAtivaEscolar\Events\ChildStatusChanged;
use BuscaAtivaEscolar\Events\SearchableNeedsReindexing;
use BuscaAtivaEscolar\Reports\Interfaces\CanBeAggregated;
use BuscaAtivaEscolar\Reports\Interfaces\CollectsDailyMetrics;
use BuscaAtivaEscolar\Reports\Traits\AggregatedBySearchDocument;
use BuscaAtivaEscolar\Scopes\TenantScope;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use BuscaAtivaEscolar\Traits\Data\Sortable;
use BuscaAtivaEscolar\Traits\Data\TenantScopedModel;
use BuscaAtivaEscolar\Search\Interfaces\Searchable;
use Carbon\Carbon;
use Geocoder\Geocoder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

use BuscaAtivaEscolar\Traits\LocationHereTrait;

/**
 * @property int $id
 *
 * @property string $name
 * @property string $tenant_id
 * @property string $city_id
 * @property string $mother_name
 * @property string $father_name
 * @property string $risk_level
 * @property string $gender
 * @property integer $age
 * @property string $alert_submitter_id
 * @property string $alert_status
 * @property string $current_case_id
 * @property string $current_step_type
 * @property string $current_step_id
 * @property string $deadline_status
 * @property string $child_status
 * @property float $lat
 * @property float $lng
 * @property string $map_region
 * @property object $map_geocoded_address
 *
 * @property Tenant $tenant
 * @property City $city
 * @property ChildCase $currentCase
 * @property ChildCase $current_case
 * @property Alerta $alert
 * @property User $submitter
 * @property CaseStep $currentStep
 * @property CaseStep $current_step
 * @property ChildCase[]|Collection $cases
 * @property Comment[]|Collection $comments
 * @property Attachment[]|Collection $attachments
 * @property ActivityLog[]|Collection $activity
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property integer $educacenso_year
 */
class Child extends Model implements Searchable, CanBeAggregated, CollectsDailyMetrics
{

    use SoftDeletes;
    use IndexedByUUID;
    use TenantScopedModel;
    use Sortable;

    use AggregatedBySearchDocument;

    use LocationHereTrait;

    const STATUS_OUT_OF_SCHOOL = "out_of_school";
    const STATUS_OBSERVATION = "in_observation";
    const STATUS_IN_SCHOOL = "in_school";
    const STATUS_CANCELLED = "cancelled";
    const STATUS_INTERRUPTED = "interrupted";
    const STATUS_TRANSFERRED = "transferred";

    const ALERT_STATUS_PENDING = "pending";
    const ALERT_STATUS_ACCEPTED = "accepted";
    const ALERT_STATUS_REJECTED = "rejected";

    const DEADLINE_STATUS_NORMAL = "normal";
    const DEADLINE_STATUS_LATE = "late";

    protected $table = "children";
    protected $fillable = [
        'name',

        'tenant_id',
        'city_id',

        'mother_name',
        'father_name',

        'risk_level',
        'gender',
        'age',

        'alert_submitter_id',
        'alert_status',

        'current_case_id',

        'current_step_type',
        'current_step_id',

        'deadline_status',
        'child_status',

        'lat',
        'lng',
        'map_region',
        'map_geocoded_address',

        'educacenso_id',
        'educacenso_year',

        'father_id'
    ];

    protected $sortable = [
        'created_at',
    ];

    protected $casts = [
        'map_geocoded_address' => 'array',
    ];

    /**
     * The tenant that "owns" this child.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tenant()
    {
        return $this->hasOne('BuscaAtivaEscolar\Tenant', 'id', 'tenant_id')->withTrashed();
    }

    /**
     * This child's origin instance city.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function city()
    {
        return $this->hasOne('BuscaAtivaEscolar\City', 'id', 'city_id');
    }

    /**
     * The current case that is dealing with this child.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentCase()
    {
        return $this->hasOne('BuscaAtivaEscolar\ChildCase', 'id', 'current_case_id');
    }

    /**
     * The alert data that originated the current case
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function alert()
    {
        // TODO: figure out how to filter this by current case
        return $this->hasOne('BuscaAtivaEscolar\CaseSteps\Alerta', 'child_id', 'id');
        //->where('case_id', $this->current_case_id);
    }

    /**
     * The pesquisa the current case
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pesquisa()
    {
        return $this->hasOne('BuscaAtivaEscolar\CaseSteps\Pesquisa', 'child_id', 'id');
    }


    public function alertCase()
    {
        // TODO: figure out how to filter this by current case
        return $this->hasOne('BuscaAtivaEscolar\CaseSteps\Alerta', 'child_id', 'id');
        //->where('case_id', $this->current_case_id);
    }

    /**
     * The user that submitted the child alert
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function submitter()
    {
        return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'alert_submitter_id');
    }

    /**
     * The current case step this child is at.
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function currentStep()
    {
        return $this->morphTo();
    }

    /**
     * Cases belonging to this child
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cases()
    {
        return $this->hasMany('BuscaAtivaEscolar\ChildCase', 'child_id', 'id');
    }

    /**
     * Comments registered on this child's file
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany('BuscaAtivaEscolar\Comment', 'content')->ordered();
    }

    /**
     * Attachments uploaded on this child's file
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments()
    {
        return $this->morphMany('BuscaAtivaEscolar\Attachment', 'content')->ordered();
    }

    /**
     * Activity log entries related to this child's file.
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activity()
    {
        return $this->morphMany('BuscaAtivaEscolar\ActivityLog', 'content')->ordered();
    }

    // ------------------------------------------------------------------------

    public function scopeAccepted($query)
    {
        return $query->where('alert_status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('alert_status', 'rejected');
    }

    public function scopePending($query)
    {
        return $query->where('alert_status', 'pending');
    }

    public function scopeHasCaseInProgress($query)
    {
        return $query
            ->where('alert_status', Child::ALERT_STATUS_ACCEPTED)
            ->whereIn('child_status', [Child::STATUS_OUT_OF_SCHOOL, Child::STATUS_OBSERVATION]);
    }

    // ------------------------------------------------------------------------

    /**
     * @param CaseStep|Model $stepClass
     * @param int $index
     * @return CaseStep|Model|null
     */
    public function getStepInCurrentCase($stepClass, $index = 0)
    {
        if (!$this->currentCase) return null;

        return $stepClass->query()
            ->where('case_id', $this->currentCase->id)
            ->skip($index)
            ->first();
    }

    /**
     * Gets the URL for viewing a child
     * @return string
     */
    public function getViewURL()
    {
        return str_finish(env('APP_PANEL_URL'), '/') . "children/view/{$this->id}";
    }

    /**
     * Gets a shorthand identifier for the child (name, gender and age).
     * Used for notifications, etc.
     * @return string
     */
    public function getShorthandIdentifier()
    {
        return ucwords($this->name)
            . ($this->gender ?
                (" / " . trans('child.gender.' . $this->gender)) : ''
            )
            . ($this->age ?
                (" / " . ($this->age != 1 ? "{$this->age} anos" : "1 ano")) : ''
            );
    }

    public function toString()
    {
        return $this->getShorthandIdentifier();
    }

    /**
     * Sets the child's current status
     * @param string $status The child status. See Child::STATUS_*
     */
    public function setStatus($status)
    {
        $prevStatus = $this->child_status;
        $this->child_status = $status;
        $this->save();

        $this->reindex();

        event(new ChildStatusChanged($this, $prevStatus, $status));
    }

    /**
     * Accepts a child alert, moving it to the list of ongoing cases
     * @param array|null $updatedDetails
     */
    public function acceptAlert(array $updatedDetails = [])
    {

        $prevStatus = $this->alert_status;
        $this->alert_status = 'accepted';

        $this->save();

        $this->currentCase->case_status = ChildCase::STATUS_IN_PROGRESS;
        $this->currentCase->save();

        $alertStep = $this->currentStep;
        /* @var $alertStep Alerta */

        if (sizeof($updatedDetails) > 0) {
            $updatedDetails = collect($updatedDetails)
                ->only(['place_address', 'place_neighborhood', 'group_id', 'group_name'])
                ->toArray();

            $alertStep->fill($updatedDetails);
        }

        $alertStep->alert_status = 'accepted';
        $alertStep->save();

        $alertStep->complete();

        event(new AlertStatusChanged($this, $prevStatus, 'accepted'));
        event(new AlertAccepted($this));
    }

    /**
     * Rejects a child alert, moving it to the list of rejected cases
     */
    public function rejectAlert()
    {
        $prevStatus = $this->alert_status;

        $this->currentCase->case_status = ChildCase::STATUS_CANCELLED;
        $this->currentCase->cancel_reason = ChildCase::CANCEL_REASON_REJECTED_ALERT;
        $this->currentCase->save();

        /**
         * atualiza o campo atert_status da tabela case_steps_alerta
         */
        $alertStep = $this->currentStep;
        $alertStep->alert_status = 'rejected';
        $alertStep->save();

        $this->alert_status = 'rejected';
        $this->child_status = self::STATUS_CANCELLED;
        $this->save();

        event(new AlertStatusChanged($this, $prevStatus, 'rejected'));
        event(new AlertRejected($this));
    }

    /**
     * Recalculates and updates the child's age through their birthday.
     * Internally users Carbon for the calculations.
     * Emits "age_updated" event.
     *
     * @param string $birthday The birthday in ISO format (YYYY-MM-DD)
     */
    public function recalculateAgeThroughBirthday(string $birthday)
    {
        $this->age = self::calculateAgeThroughBirthday($birthday);
        $this->save();

        event("child.age_updated", [$this]);
    }

    /**
     * Updates the child's latitude and longitude based on a raw address string
     * @param string $rawAddress The full address to geocode
     * @return \Geocoder\Model\Address|null The geocoded address, or null if failed
     */
    public function updateCoordinatesThroughGeocoding($rawAddress)
    {
        //        $geocoder = app('geocoder');
        //        /* @var $geocoder Geocoder */
        //        $address = null;
        //        $endPoint = "https://geocoder.ls.hereapi.com/6.2/geocode.json";
        //        $key = env('HERE_API_KEY');
        //        $client = new \GuzzleHttp\Client();
        //        $url = $endPoint . '?searchtext=' . $rawAddress . '&gen=9&apiKey=' . $key;

        //        $address = null;
        //        $endPoint = "https://geocoder.ls.hereapi.com/6.2/geocode.json";
        //        $key = env('HERE_API_KEY');
        //        $client = new \GuzzleHttp\Client();
        //        $url = $endPoint . '?searchtext=' . $rawAddress . '&gen=9&apiKey=' . $key;
        //
        //        $request = $client->request('GET', $endPoint . '?searchtext=' . $rawAddress . '&gen=9&apiKey=' . $key);
        //        $stream = json_decode($request->getBody()->getContents());
        //        $location = $stream->Response->View[0]->Result[0]->Location;

        $location = $this->getLocationByRawAddress($rawAddress);

        if ($location) {
            if ($location->Address->Country == 'BRA') {
                $this->update([
                    'lat' => ($location->DisplayPosition) ? $location->DisplayPosition->Latitude : null,
                    'lng' => ($location->DisplayPosition) ? $location->DisplayPosition->Longitude : null,
                    'map_geocoded_address' => ($location) ? $location : null,
                ]);
            } else {
                $this->update([
                    'lat' => null,
                    'lng' => null,
                    'map_geocoded_address' => null,
                ]);
            }
        } else {
            $this->update([
                'lat' => null,
                'lng' => null,
                'map_geocoded_address' => null,
            ]);
        }

        return $location;
    }

    // ------------------------------------------------------------------------

    public function getSearchIndex(): string
    {
        return config('search.index_prefix') . 'children';
    }

    public function getSearchType(): string
    {
        return 'child';
    }

    public function getSearchID()
    {
        return $this->id;
    }

    /**
     * Triggers a document reindex for the child.
     */
    public function reindex()
    {
        event(new SearchableNeedsReindexing($this->fresh()));
    }

    /**
     * Builds the searchable document for the child.
     * @return array
     * @throws \Exception
     */
    public function buildSearchDocument(): array
    {

        if (!$this->exists) {
            return 'null';
        }

        $data = [];

        if ($this->currentCase) {
            $steps = $this->currentCase->fetchSteps();
            /* @var $steps CaseStep[] */

            foreach ($steps as $step) {
                $fields = $step->getFields();
                $data = array_filter($fields)
                    + array_filter($data)
                    + array_fill_keys(array_keys($fields) + array_keys($data), null);
            }
        }

        $data = $this->getAttributes() + $data;


        if ($this->currentStep) {
            $data['assigned_user_id'] = $this->currentStep->assigned_user_id ?? null;

            $assignedUser = $this->currentStep->assigned_user_id ? // TODO: refactor the way we deal with non-scoped models
                User::withoutGlobalScope(TenantScope::class)->find($data['assigned_user_id']) : null;

            $data['assigned_user_name'] = $assignedUser->name ?? null;

            //Check if User Assigned to case is restricted to UF. The property assigned_uf there is only in cases assigned to UF
            if ($assignedUser !== null) {
                if ($assignedUser->isRestrictedToUF()) {
                    $data['assigned_uf'] = $assignedUser->uf ?? null;
                } else {
                    $data['assigned_uf'] = null;
                }
            } else {
                $data['assigned_uf'] = null;
            }

            $data['assigned_group_name'] = $assignedUser->group->name ?? null;

            $data['step_name'] = $this->currentStep->getName() ?? null;
            $data['step_slug'] = str_slug($this->currentStep->getName(), '_') ?? null;

            $now = Carbon::now();
            // O tenant pode ser nulo quando o mesmo foi desativado
            if (!empty($this->tenant)) {
                $deadline = $this->tenant->getDeadlineFor($this->currentStep->getSlug());

                if ($this->currentStep->isLate($now, $deadline)) {
                    $data['deadline_status'] = "late";

                    //We need this rule because the step GESTAO DO CASO has not a pattern deadline
                    if ($this->currentStep->getSlug() === "gestao_do_caso") {
                        $data['deadline_status'] = "normal";
                    }
                } else {
                    $data['deadline_status'] = "normal";
                }
            }
        }

        if ($this->submitter) {
            $data['alert_submitter_name'] = $this->submitter->name;
        }

        if ($this->currentCase) {

            $data['case_status'] = $this->currentCase->case_status;
            $data['cancel_reason'] = $this->currentCase->cancel_reason;

            if ($this->currentCase->case_cause_ids) { // TODO: refactor this
                $data['cause_name'] = join(", ", array_map(function ($cause_id) {
                    return CaseCause::getByID(intval($cause_id))->label ?? '';
                }, $this->currentCase->case_cause_ids));
            } else if ($this->currentCase->alert_cause_id) {
                $data['cause_name'] = AlertCause::getByID(intval($this->currentCase->alert_cause_id))->label ?? '';
            }

            $data['group_id'] = $this->currentCase->group ? $this->currentCase->group->id : null;
            $data['group_name'] = $this->currentCase->group ? $this->currentCase->group->name : null;

            if( $this->currentCase->group ){
                $data['tree_id'] = implode(', ', Group::where('id', $this->currentCase->group->id)->get()->first()->getArrayOfParentsId());
            }
        }

        $data['city_name'] = $this->city->name ?? null;
        $data['uf'] = $this->city->uf ?? null;
        $data['country_region'] = $this->city->region ?? null;

        return $data;
    }

    // ------------------------------------------------------------------------

    public function getTimeSeriesIndex(): string
    {
        return config('search.index_prefix') . 'children_daily';
    }

    public function getTimeSeriesType(): string
    {
        return 'child';
    }

    /**
     * Builds a documents with aggregable & filterable metrics for the reporting system
     * @return array
     * @throws \Exception
     */
    public function buildMetricsDocument(): array
    {

        return Arr::only($this->buildSearchDocument(), [
            "id",
            "tenant_id",
            "city_id",
            "child_status",
            "case_status",
            "alert_status",
            "deadline_status",
            "age",
            "gender",
            "risk_level",
            "step_slug",
            "is_child_still_in_school",
            "reinsertion_grade",
            "school_city_id",
            "school_id",
            "school_uf",
            "race",
            "has_been_in_school",
            "school_last_grade",
            "school_last_year",
            "school_last_status",
            "school_last_age",
            "is_working",
            "work_activity",
            "work_is_paid",
            "work_weekly_hours",
            "parents_has_mother",
            "parents_has_grandparents",
            "parents_has_father",
            "parents_has_brother",
            "parents_has_others",
            "parents_who_is_guardian",
            "parents_income",
            "guardian_race",
            "guardian_schooling",
            "case_cause_ids",
            "handicapped_reason_not_enrolled",
            "handicapped_at_sus",
            "place_city_id",
            "place_uf",
            "place_kind",
            "place_is_quilombola",
            "school_last_id",
            "alert_cause_id",
            "assigned_user_id",
            "alert_submitter_id",
            "uf",
            "assigned_uf",
            "country_region",
        ]);
    }

    // ------------------------------------------------------------------------

    /**
     * Calculates a children's age by their birthday.
     * @param string $birthday The birthday in ISO format (YYYY-MM-DD)
     * @return int|null The age, in years. Will return null if no/invalid date is given.
     */
    public static function calculateAgeThroughBirthday(string $birthday)
    {
        if (!$birthday) return null;

        $dob = Carbon::createFromFormat('Y-m-d', $birthday);

        if (!$dob) return null;

        return $dob->diffInYears();
    }

    /**
     * Creates a new Child case chain by data received by an alert.
     * This will create a new child, with a new ChildCase and the default CaseStep structure.
     *
     * @param Tenant $tenant The tenant this child will be attached to
     * @param mixed $creatorUserID The ID of the user that created this alert (null if not available)
     * @param array $data The data received
     * @return Child
     */
    public static function spawnFromAlertData(Tenant $tenant, $creatorUserID = null, array $data)
    {

        $data['tenant_id'] = $tenant->id;
        $data['city_id'] = $tenant->city_id;

        $data['created_by_user_id'] = $creatorUserID;
        $data['alert_submitter_id'] = $creatorUserID;

        $data['child_status'] = self::STATUS_OUT_OF_SCHOOL;
        $data['alert_status'] = self::ALERT_STATUS_PENDING;
        $data['risk_level'] = $tenant->getSettings()->getAlertPriority($data['alert_cause_id']) ?? ChildCase::RISK_LEVEL_MEDIUM;

        if (!array_key_exists("group_id", $data)) $data['group_id'] = $tenant->primary_group_id;

        $child = self::create($data);

        $case = ChildCase::spawn($child, $data);
        $alertStep = $case->currentStep;

        $child->current_case_id = $case->id;
        $child->current_step_type = $alertStep->step_type;
        $child->current_step_id = $alertStep->id;

        if (isset($data['dob'])) {
            $child->age = self::calculateAgeThroughBirthday($data['dob']);
        }

        $child->save();

        $alertStep->fill($data);
        $alertStep->assigned_user_id = $creatorUserID;
        $alertStep->save();

        event(new AlertSpawned($child, $case, $alertStep));

        return $child;
    }

    /**
     * Gets all children with active cases
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getAllActive()
    {
        return self::query()
            ->with(['tenant', 'currentStep'])
            ->whereIn('child_status', [self::STATUS_OUT_OF_SCHOOL, self::STATUS_OBSERVATION])
            ->where('alert_status', self::ALERT_STATUS_ACCEPTED)
            ->get();
    }

    /**
     * Gets all children of the actual child
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getChild()
    {
        return $this->hasOne('BuscaAtivaEscolar\Child', 'father_id');
    }

    /**
     * Gets the parent of actual child
     * @return \BuscaAtivaEscolar\Child
     */
    public function getParent()
    {
        return $this->belongsTo('BuscaAtivaEscolar\Child', 'father_id');
    }

    /**
     *
     */
    public function getReopens()
    {
        $value = [];
        $idForGetFather = $this->father_id ? $this->father_id : 0;
        $idForGetSon = $this->id ? $this->id : 0;


        $reopeningRequestFather = ReopeningRequests::where(
            ['child_id' => $idForGetFather]
        )->first();

        $reopeningRequestSon = ReopeningRequests::where(
            ['child_id' => $idForGetSon]
        )->first();

        if ($reopeningRequestFather || $reopeningRequestSon) {
            /* @var $user User */
            $user = \Auth::user();
            $user->type = User::TYPE_GESTOR_NACIONAL;
        }

        $sons = $this->getSon($idForGetSon, $value);
        $fathers = $this->getFather($idForGetFather, $value);
        return array_merge($fathers, $sons);;
    }

    private function getFather($id, $value)
    {
        if (!empty($id)) {
            $child = Child::where('id', $id)->first();
            array_push($value, ['id' => $child->id, 'name' => $child->name, 'created_at' => $child->created_at->toIso8601String(), 'child_status' => $child->child_status, 'reason' => $this->getReason($child->id)]);
            return $this->getFather($child->father_id, $value);
        }
        return $value;
    }

    private function getSon($id, $value)
    {
        if (!empty($id)) {
            $child = Child::where('father_id', $id)->first();
            if (empty($child)) {
                return $value;
            }
            array_push($value, ['id' => $child->id, 'name' => $child->name, 'created_at' => $child->created_at->toIso8601String(), 'child_status' => $child->child_status]);
            return $this->getSon($child->id, $value);
        }
        return $value;
    }

    private function getReason($childId)
    {
        $case = ChildCase::where('child_id', $childId)->first();
        if ($case->interrupt_reason == 'request') {
            /* @var $reopeningRequest ReopeningRequests */
            $reopeningRequest = ReopeningRequests::where(
                ['child_id' => $childId]
            )->first();
            $case->interrupt_reason = $reopeningRequest->interrupt_reason;
        }
        return $case->interrupt_reason;
    }
}
