<?php

/**
 * busca-ativa-escolar-api
 * Group.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 23/01/2017, 18:10
 */

namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\Settings\GroupSettings;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use BuscaAtivaEscolar\Traits\Data\TenantScopedModel;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 *
 * @property string $tenant_id
 * @property string $uf
 * @property string $name
 * @property string $is_primary
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property Tenant $tenant
 * @property User[]|Collection $users
 * @property-read GroupSettings $settings
 */
class Group extends Model
{

	use IndexedByUUID;
	use SoftDeletes;
	use TenantScopedModel;

	protected $table = "groups";
	protected $fillable = [
		'tenant_id',
		'parent_id',
		'name',
		'is_primary',
	];

	protected $casts = [
		'is_primary' => 'boolean'
	];

	protected $keyType = 'string';

	// -----------------------------------------------------------------------------------------------------------------

	/**
	 * The tenant this user group belongs to.
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function tenant()
	{
		return $this->hasOne('BuscaAtivaEscolar\Tenant', 'id', 'tenant_id');
	}

	/**
	 * The group parent this group belongs to.
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function parent()
	{
		return $this->hasOne('BuscaAtivaEscolar\Group', 'id', 'parent_id');
	}

	/**
	 * The the groups users belonging in this user group.
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function children()
	{
		return $this->hasMany('BuscaAtivaEscolar\Group', 'parent_id', 'id');
	}

	/**
	 * The users belonging in this user group.
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function users()
	{
		return $this->hasMany('BuscaAtivaEscolar\User', 'group_id', 'id');
	}

	/**
	 * The cases belonging in this group.
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function cases()
	{
		return $this->hasMany('BuscaAtivaEscolar\ChildCase', 'group_id', 'id');
	}

	// -----------------------------------------------------------------------------------------------------------------

	/**
	 * Updates the group settings object
	 * @param GroupSettings $settings
	 */
	public function setSettings(GroupSettings $settings)
	{
		$this->settings = $settings->serialize();
		$this->save();

		self::updateCausesMap($this);
	}

	/**
	 * Gets the group settings object
	 * @return GroupSettings
	 */
	public function getSettings()
	{
		if (!$this->settings) return new GroupSettings($this);
		return GroupSettings::unserialize($this->settings);
	}

	// -----------------------------------------------------------------------------------------------------------------

	/**
	 * Creates the default primary group for a tenant (Secretaria da Educação)
	 * @param Tenant $tenant
	 * @return Group
	 */
	public static function createDefaultPrimaryGroup(Tenant $tenant)
	{
		return self::create([
			'tenant_id' => $tenant->id,
			'name' => 'BAE',
			'is_primary' => true
		]);
	}

	public static function createThree(Tenant $tenant, $name, $parent)
	{
		return self::create([
			'tenant_id' => $tenant->id,
			'name' => $name,
			'is_primary' => false,
			'parent_id' => $parent
		]);
	}

	/**
	 * Updates the map of alert causes handled per group for a specific group
	 * @param Group $group
	 */
	public static function updateCausesMap(Group $group)
	{
		if (!$group->tenant_id) return;

		$map = DB::table("group_causes");
		$causes = $group->getSettings()->getHandledAlertCauses();

		$map->where('group_id', $group->id)->delete();

		$map->insert(array_map(function ($cause_id) use ($group) {
			return ['tenant_id' => $group->tenant_id, 'group_id' => $group->id, 'alert_cause_id' => $cause_id];
		}, $causes));
	}

	/**
	 * Gets a list of groups that are assigned to a certain alert cause
	 * @param Tenant $tenant The tenant to scope the query with
	 * @param integer $alert_cause_id The ID of the alert cause (@see AlertCause)
	 * @return int[]
	 */
	public static function getGroupIDsByAlertCause(Tenant $tenant, $alert_cause_id)
	{
		return DB::table("group_causes")
			->select('group_id')
			->where('tenant_id', $tenant->id)
			->where('alert_cause_id', $alert_cause_id)
			->pluck('group_id')
			->toArray();
	}

    public function getArrayOfParentsId(){
		if($this->parent['parent']['parent'])
			return [$this->parent->parent->parent['id'], $this->parent->parent['id'], $this->parent['id'], $this->id];
		if($this->parent['parent']) 
			return [$this->parent->parent['id'], $this->parent['id'], $this->id];
        if($this->parent)
			return [$this->parent['id'], $this->id];
        return [$this->id];
    }

}
