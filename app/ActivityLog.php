<?php
/**
 * busca-ativa-escolar-api
 * ActivityLog.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 19/01/2017, 14:10
 */

namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use BuscaAtivaEscolar\Traits\Data\TenantScopedModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityLog extends Model {

	use SoftDeletes;
	use IndexedByUUID;
	use TenantScopedModel;
    // Conexão externa para a tabela activity log
    //protected $connection = 'mysql2';
	protected $table = "activity_log";
	protected $fillable = [
		'tenant_id',
		'user_id',
		'content_type',
		'content_id',
		'action',
		'parameters',
		'metadata',
	];

	protected $casts = [
		'parameters' => 'object',
		'metadata' => 'object',
	];

	// -----------------------------------------------------------------------------------------------------------------

	/**
	 * The tenant that owns this log entry. May be null if the content object is not tenant-scoped.
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function tenant() {
		return $this->hasOne('BuscaAtivaEscolar\Tenant', 'id', 'tenant_id');
	}

	/**
	 * The user who performed the action this entry log refers to.
	 * May be null if the activity was performed by a command/bot/etc.
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function user() {
		return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'user_id');
	}

	/**
	 * The content this log entry is attached to. Is an instance of Eloquent's Model. May or may not be tenant-scoped.
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function content() {
		return $this->morphTo('content');
	}

	// -----------------------------------------------------------------------------------------------------------------

	/**
	 * Scope: orders by date, descending
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeOrdered($query) {
		return $query->orderBy('created_at', 'DESC');
	}

	// -----------------------------------------------------------------------------------------------------------------

	/**
	 * Fetches the last activity log entries for the specified model.
	 *
	 * @param Model $content The parent content, whose activity are related to
	 * @param int $max The maximum entries to fetch. If given 0, will return all.
	 * @param bool $onlyVisibleEntries Filter only "visible" actions? Those are configured in config/activity_log.php
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public static function fetchEntries(Model $content, int $max = 10, bool $onlyVisibleEntries = true) {

		$query = self::query()
			->where('content_id', $content->id)
			->where('content_type', get_class($content))
			->orderBy('created_at', 'DESC');

		if($max > 0) {
			$query->limit($max);
		}

		if($onlyVisibleEntries) {
			$query->whereIn('action', config('activity_log.visible_events.' . get_class($content), []));
		}

		return $query->get();
	}

	/**
	 * Registers an activity in the activity log
	 * @param Model $content The target content the activity was performed on
	 * @param string $action The code of the activity that was performed. All similar actions must share the action code.
	 * @param array $parameters The parameters of the action, such as in what context it happened, with what other entities, etc.
	 * @param array $metadata Any relevant action metadata (current environment, ip address, etc)
	 * @return ActivityLog The created activity log entry
	 */
	public static function writeEntry(Model $content, $action, $parameters = [], $metadata = []) {
		$entry = new ActivityLog();
		$entry->tenant_id = $content->tenant_id ?? null;
		$entry->content_type= get_class($content);
		$entry->content_id = $content->id;

		if(auth()->check()) {
			$entry->user_id = auth()->user()->id;
			$metadata['user'] = [
				'id' => auth()->user()->id,
				'name' => auth()->user()->name,
				'type' => auth()->user()->type,
				'email' => auth()->user()->email,
				'group_id' => auth()->user()->group_id,
				'tenant_id' => auth()->user()->tenant_id,
			];
		}

		$entry->action = $action;
		$entry->parameters = $parameters;
		$entry->metadata = $metadata;
		$entry->save();

		// TODO: register on ElasticSearch?

		return $entry;
	}

	/**
	 * Fetches the latest activity on all children
	 *
	 * @param string $contentType The content type to search activity for
	 * @param integer $max The maximum amount of activities to return
	 * @param boolean $onlyVisibleEntries Only display 'visible' entries in the log
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
	 */
	public static function fetchRecentEntries($contentType = null, $max = 10, $onlyVisibleEntries = true) {
		$query = self::query();

		if($contentType !== null) {
			$query->where('content_type', $contentType);
		}

		if($onlyVisibleEntries) {
			$query->whereIn('action', config('activity_log.visible_events.' . ($contentType ?? 'global'), []));
		}

		$query
			->orderBy('created_at', 'DESC')
			->take($max);

		return $query->get();
	}

}