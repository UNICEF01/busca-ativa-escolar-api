<?php
/**
 * busca-ativa-escolar-api
 * Tenant.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/12/2016, 20:26
 */

namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\Exceptions\ValidationException;
use BuscaAtivaEscolar\Mail\UserRegisterNotification;
use BuscaAtivaEscolar\Mailables\UserCredentialsForNewTenant;
use BuscaAtivaEscolar\Settings\TenantSettings;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use BuscaAtivaEscolar\Traits\Data\Sortable;
use Cache;
use Carbon\Carbon;
use DB;
use Geocoder\Geocoder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Log;
use Mail;

/**
 * @property int $id
 *
 * @property string $name
 * @property string $name_ascii
 * @property string $city_id
 * @property string $uf
 * @property string $operational_admin_id
 * @property string $political_admin_id
 * @property string $primary_group_id
 * @property boolean $is_registered
 * @property boolean $is_active
 * @property boolean $is_setup
 * @property Carbon|null $last_active_at
 * @property Carbon|null $registered_at
 * @property Carbon|null $activated_at
 * @property float $map_lat
 * @property float $map_lng
 *
 * @property City $city
 * @property Group $primary_group
 * @property TenantSettings $settings
 * @property User $politicalAdmin
 * @property User $operationalAdmin
 *
 * @property object $educacenso_import_details
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class Tenant extends Model  {

	use IndexedByUUID;
	use SoftDeletes;
	use Sortable;

	protected $table = "tenants";

	protected $fillable = [
		'name',
		'name_ascii',
		'city_id',
		'uf',

		'operational_admin_id',
		'political_admin_id',
		'primary_group_id',

		'is_registered',
		'is_active',
		'is_setup',

		'last_active_at',

		'registered_at',
		'activated_at',
		
		'map_lat',
		'map_lng',

		'educacenso_import_details',

        'goal',
        'is_state'
	];

	protected $casts = [
		'is_registered' => 'boolean',
		'is_active' => 'boolean',

		'educacenso_import_details' => 'object',

		'last_active_at' => 'datetime',
		'registered_at' => 'datetime',
		'activated_at' => 'datetime'
	];

	// ------------------------------------------------------------------------

	/**
	 * The operational admin ("coordenador operacional") for this instance
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function operationalAdmin() {
		return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'operational_admin_id')->withTrashed();
	}

	/**
	 * The political admin ("gestor político") for this instance
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function politicalAdmin() {
		return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'political_admin_id')->withTrashed();
	}

	/**
	 * The primary/default group for users
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function primaryGroup() {
		return $this->hasOne('BuscaAtivaEscolar\Group', 'id', 'primary_group_id');
	}

	/**
	 * The city this tenant is associated with
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function city() {
		return $this->hasOne('BuscaAtivaEscolar\City', 'id', 'city_id');
	}

    /**
     * The users associated with this tenant
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(){
	    return $this->hasMany('BuscaAtivaEscolar\User', 'tenant_id', 'id');
    }

	/**
	 * Internal, primary key for API routing.
	 * @return string
	 */
	public function getRouteKeyName() {
		return 'id';
	}

	/**
	 * Gets the expected deadline for a step by its slug, as configured in the tenant
	 * @param string $step_slug
	 * @return int
	 */
	public function getDeadlineFor($step_slug) {
		return $this->getSettings()->stepDeadlines[$step_slug] ?? 0;
	}

	/**
	 * Gets the Tenant's map center coordinates, or null if none found
	 * @return array|null
	 */
	public function getMapCoordinates() {
		if(!$this->map_lat || !$this->map_lng) {
			$geocoder = app('geocoder'); /* @var $geocoder Geocoder */

			$place = $geocoder->geocode("{$this->name} - Brasil")->get()->first();

			if(!$place) {
				Log::error("Failed to geocode tenant map center: {$this->name}");
				return null;
			}

			$this->update(['map_lat' => $place->getLatitude(), 'map_lng' => $place->getLongitude()]);
		}

		return ['lat' => $this->map_lat, 'lng' => $this->map_lng, 'zoom' => 10];
	}

	/**
	 * Checks if the tenant is considered "active" (activity in the last 30 days)
	 * @return bool
	 */
	public function isActive() {
		if(!$this->last_active_at) {
			return false;
		}

		return abs($this->last_active_at->diffInDays(Carbon::now())) >= 30;
	}

	/**
	 * Updates the 'last active at' datetime
	 */
	public function tickLastActivity() {
		$this->last_active_at = Carbon::now();
		// Done using DB so we don't modify "updated_at" column
		DB::update("UPDATE tenants SET last_active_at = ? WHERE id = ?", [$this->last_active_at->toDateTimeString(), $this->id]);
	}

	/**
	 * Generates an array to export as XLS
	 * @return array
	 */
	public function toExportArray() {
		return [
			'Nome' => $this->name,
			'UF' => $this->uf,
//			'Gestor Operacional - Nome' => $this->politicalAdmin->name ?? null,
//			'Gestor Operacional - Email' => $this->operationalAdmin->email ?? null,
//			'Gestor Operacional - Telefone' => $this->operationalAdmin ? $this->operationalAdmin->getContactPhone() : null,
//			'Gestor Político - Nome' => $this->politicalAdmin->name ?? null,
//			'Gestor Político - Email' => $this->politicalAdmin->email ?? null,
//			'Gestor Político - Telefone' => $this->politicalAdmin ? $this->politicalAdmin->getContactPhone() : null,
			'Está ativo?' => $this->last_active_at->diffInDays(Carbon::now()) >= 30 ? 'Inativo' : 'Ativo',
            'Data última atividade' => $this->last_active_at ? $this->last_active_at->format('d/m/Y') : null,
            'Tempo' => $this->last_active_at ? $this->last_active_at->diffInDays(Carbon::now()).' dias' : null,
            'Está configurado?' => $this->is_setup ? "Sim" : "Não",
			'Data cadastro' => $this->registered_at ? $this->registered_at->format('d/m/Y') : null,
			'Data ativação' => $this->activated_at ? $this->activated_at->format('d/m/Y') : null,
            'Data exclusão' => $this->deleted_at ? $this->deleted_at : null
		];
	}

	// ------------------------------------------------------------------------

	/**
	 * Updates the tenant settings object
	 * @param TenantSettings $settings
	 */
	public function setSettings(TenantSettings $settings) {
		$this->settings = $settings->serialize();
		$this->save();
	}

	/**
	 * Gets the tenant's current settings
	 * @return TenantSettings
	 */
	public function getSettings() {
		if(!$this->settings) return new TenantSettings();
		return TenantSettings::unserialize($this->settings);
	}

	// ------------------------------------------------------------------------

	/**
	 * Generates a display name for a tenant, based on city UF code and name.
	 * Formats using national standards (UF always uppercase, city name with each word first char uppercase).
	 * @param City $city The city the tenant will be associated with
	 * @return string
	 */
	public static function generateNameFromCity(City $city) {
		return strtoupper($city->uf) . ' / ' . ucwords($city->name);
	}

	/**
	 * Provisions a tenant based on sign-up data
	 *
	 * @param TenantSignup $signup
	 * @param array $politicalAdminData
	 * @param array $operationalAdminData
	 *
	 * @throws \Exception on failure
	 *
	 * @returns Tenant
	 */
	public static function provision(TenantSignup $signup, array $politicalAdminData, array $operationalAdminData) {

		$city = $signup->city;

		if(!$city) {
			throw new ValidationException('invalid_signup_city');
		}

		if(User::checkIfExists($politicalAdminData['email'])) {
			throw new ValidationException('political_admin_email_in_use');
		}

        if(User::checkIfExists($operationalAdminData['email'])) {
            throw new ValidationException('operational_admin_email_in_use');
        }

		$now = date('Y-m-d H:i:s');

		$name = Tenant::generateNameFromCity($city);

		$tenant = Tenant::create([
			'uf' => $city->uf,
			'name' => $name,
			'name_ascii' => strtolower(Str::ascii($name)),
			'city_id' => $city->id,
			'operational_admin_id' => null,
			'political_admin_id' => null,
			'is_registered' => false,
			'is_active' => false,
			'last_active_at' => $now,
			'registered_at' => $signup->created_at,
			'activated_at' => $now,
		]);

		$politicalAdminData['type'] = User::TYPE_GESTOR_POLITICO;
		$politicalAdminData['uf'] = $tenant->uf;
		$politicalAdminData['tenant_id'] = $tenant->id;

		$operationalAdminData['type'] = User::TYPE_GESTOR_OPERACIONAL;
		$operationalAdminData['tenant_id'] = $tenant->id;
		$operationalAdminData['uf'] = $tenant->uf;

		$politicalAdmin = new User();
		$politicalAdmin->fill($politicalAdminData);
		$politicalAdmin->password = password_hash($politicalAdminData['password'], PASSWORD_DEFAULT);

		$validator = $politicalAdmin->validate($politicalAdminData, true, true, false);

		if($validator->fails()) {
			$tenant->delete();
			throw new ValidationException('invalid_political_admin_data', $validator);
		}

		$operationalAdmin = new User();
		$operationalAdmin->fill($operationalAdminData);
		//default password
		$operationalAdmin->password = password_hash( date_timestamp_get(date_create()), PASSWORD_DEFAULT);

		$validator = $operationalAdmin->validate($operationalAdminData, true, true, false);

		if($validator->fails()) {
			$tenant->delete();
			throw new ValidationException('invalid_operational_admin_data', $validator);
		}

		$primaryGroup = Group::createDefaultPrimaryGroup($tenant);

		$politicalAdmin->lgpd = 0;
        $operationalAdmin->lgpd = 0;

		$politicalAdmin->group_id = $primaryGroup->id;
        $operationalAdmin->group_id = $primaryGroup->id;

		$politicalAdmin->save();
		$operationalAdmin->save();

		$tenant->political_admin_id = $politicalAdmin->id;
		$tenant->operational_admin_id = $operationalAdmin->id;
		$tenant->primary_group_id = $primaryGroup->id;
		$tenant->is_registered = true;
		$tenant->is_active = true;
		$tenant->save();

		$signup->is_provisioned = true;
		$signup->tenant_id = $tenant->id;
		$signup->save();

		Cache::forget('uf_tenants_' . $tenant->uf);

		Mail::to($politicalAdmin->email)->send(new UserCredentialsForNewTenant($signup, $tenant, $politicalAdmin, $politicalAdminData['password']));
		Mail::to($operationalAdmin->email)->send(new UserRegisterNotification($operationalAdmin, UserRegisterNotification::TYPE_REGISTER_INITIAL));

		return $tenant;

	}

    /**
     * Recovere a tenant based on sign-up data
     *
     * @param TenantSignup $signup
     * @param array $politicalAdminData
     * @param array $operationalAdminData
     *
     * @throws \Exception on failure
     *
     * @returns Tenant
     */
    public static function recovere(TenantSignup $signup, array $politicalAdminData, array $operationalAdminData, $lastTenant, array $lastCoordinators) {

        $city = $signup->city;

        if(!$city) {
            throw new ValidationException('invalid_signup_city');
        }

        if(User::checkIfExists($politicalAdminData['email'])) {
            throw new ValidationException('political_admin_email_in_use');
        }

        if(sizeof($operationalAdminData) > 0){
            if(User::checkIfExists($operationalAdminData['email'])) {
                throw new ValidationException('operational_admin_email_in_use');
            }
        }

        foreach ($lastCoordinators as $coordinator){
            if(User::checkIfExists($coordinator['email'])) {
                throw new ValidationException('coordinator_email_in_use');
            }
        }

        $now = date('Y-m-d H:i:s');

        //Recupera o antigo tenant
        $tenant = Tenant::withTrashed()->where('id', '=', $lastTenant['id'])->first();

        //Cria a estutura dos dados do novo Gestor político
        $politicalAdminData['type'] = User::TYPE_GESTOR_POLITICO;
        $politicalAdminData['uf'] = $tenant->uf;
        $politicalAdminData['tenant_id'] = $tenant->id;

        $politicalAdmin = new User();
        $politicalAdmin->fill($politicalAdminData);
        $politicalAdmin->password = password_hash($politicalAdminData['password'], PASSWORD_DEFAULT);

        $validator = $politicalAdmin->validate($politicalAdminData, true, true, false);

        if($validator->fails()) {
            throw new ValidationException('invalid_political_admin_data', $validator);
        }

        $politicalAdmin->lgpd = 0;

        //cria a estrutura do novo coordenador se existir
        if(sizeof($operationalAdminData) > 0){

            $operationalAdminData['type'] = User::TYPE_GESTOR_OPERACIONAL;
            $operationalAdminData['tenant_id'] = $tenant->id;
            $operationalAdminData['uf'] = $tenant->uf;

            $operationalAdmin = new User();
            $operationalAdmin->fill($operationalAdminData);
            //default password
            $operationalAdmin->password = password_hash(date_timestamp_get(date_create()), PASSWORD_DEFAULT);

            $validator = $operationalAdmin->validate($operationalAdminData, true, true, false);

            if($validator->fails()) {
                throw new ValidationException('invalid_operational_admin_data', $validator);
            }

            $politicalAdmin->save();
            Mail::to($politicalAdmin->email)->send(new UserCredentialsForNewTenant($signup, $tenant, $politicalAdmin, $politicalAdminData['password']));

            $operationalAdmin->lgpd = 0;

            $operationalAdmin->save();
            Mail::to($operationalAdmin->email)->send(new UserRegisterNotification($operationalAdmin, UserRegisterNotification::TYPE_REGISTER_INITIAL));

            $tenant->operational_admin_id = $operationalAdmin->id;

        }else{
            $politicalAdmin->save();
            Mail::to($politicalAdmin->email)->send(new UserCredentialsForNewTenant($signup, $tenant, $politicalAdmin, $politicalAdminData['password']));
        }

        $tenant->political_admin_id = $politicalAdmin->id;
        $tenant->is_registered = true;
        $tenant->is_active = true;
        $tenant->last_active_at = $now;
        $tenant->activated_at = $now;
        $tenant->deleted_at = null;
        $tenant->save();

        $signup->is_provisioned = true;
        $signup->tenant_id = $tenant->id;
        $signup->save();

        Cache::forget('uf_tenants_' . $tenant->uf);

        //reativa usuários e encaminha mensagens
        foreach ($lastCoordinators as $coordinator){
            if( array_key_exists("active", $coordinator) ) {
                if( $coordinator['active'] ){
                    $registeredCoordenator = User::onlyTrashed()->where('id', '=', $coordinator['id'])->first();
                    $registeredCoordenator->email = $coordinator['email'];
                    $registeredCoordenator->lgpd = 0;
                    $registeredCoordenator->save();
                    $registeredCoordenator->restore();
                    Mail::to($registeredCoordenator->email)->send(new UserRegisterNotification($registeredCoordenator, UserRegisterNotification::TYPE_REGISTER_REACTIVATION));
                }
            }
        }

        return $tenant;

    }

	/**
	 * Gets a list of all tenant IDs within a specific state
	 * @param string $uf
	 * @return array
	 */
	public static function getIDsWithinUF($uf) {
		return Cache::remember('uf_tenants_' . $uf, config('cache.timeouts.uf_tenants'), function () use ($uf) {
			return DB::table('tenants')
				->where('uf', $uf)
				->get(['id'])
				->pluck('id')
				->toArray();
		});
	}

}