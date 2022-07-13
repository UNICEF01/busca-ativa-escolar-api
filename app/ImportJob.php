<?php
/**
 * busca-ativa-escolar-api
 * ImportJob.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 05/04/2017, 16:48
 */

namespace BuscaAtivaEscolar;

use BuscaAtivaEscolar\Importers\EducacensoCVSImporter;
use BuscaAtivaEscolar\Importers\EducacensoXLSChunkImporter;
use BuscaAtivaEscolar\Importers\XLSFileChildrenImporter;
use BuscaAtivaEscolar\Importers\EducacensoXLSImporter;
use BuscaAtivaEscolar\Importers\SchoolCSVImporter;
use BuscaAtivaEscolar\Importers\Importer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 *
 * @property string $type
 * @property string $status
 * @property string $path
 * @property array $errors
 * @property string $user_id
 * @property string $tenant_id
 * @property object $metadata
 * @property integer $offset
 * @property integer $total_records
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property User|null $user
 * @property Tenant|null $tenant
 */
class ImportJob extends Model {

	const PUBLIC_FIELDS = [
		'id',
		'created_at',

		'type',
		'status',
		'user_id',
		'tenant_id',

		'path',

		'offset',
		'total_records',
        'duplicateds',
	];

	const TYPES = [
		'school_csv' => SchoolCSVImporter::class,
		'inep_educacenso_xls' => EducacensoXLSImporter::class,
        'inep_educacenso_csv' => EducacensoCVSImporter::class,
        'inep_educacenso_xls_chunck' => EducacensoXLSChunkImporter::class,
        'xls_file_children' => XLSFileChildrenImporter::class
	];

    const STATUS_PENDING = "pending";
    const STATUS_VALIDATING = "validating";
    const STATUS_PROCESSING = "processing";
	const STATUS_COMPLETED = "completed";
	const STATUS_FAILED = "failed";

	protected $table = "import_jobs";

	protected $fillable = [
		'type',
		'status',

		'path',
		'errors',

		'user_id',
		'tenant_id',

		'metadata',

		'offset',
		'total_records',
        'duplicateds'
	];

	protected $casts = [
		'offset' => 'integer',
		'total_records' => 'integer',
		'errors' => 'array',
		'metadata' => 'object',
        'duplicateds' => 'object'
	];

	public function user() {
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	public function tenant() {
		return $this->hasOne(Tenant::class, 'id', 'tenant_id');
	}

	public function storeError(\Exception $ex) {
		if(!$this->errors) $this->errors = [];

		$errors = $this->errors;
		array_push($errors, $ex->getMessage());
		$this->errors = $errors;
	}

	public function setOffset($offset) {
		$this->update(['offset' => $offset]);
	}

	public function setStatus($status) {
		$this->update(['status' => $status]);
	}

	public function setTotalRecords($total_records) {
		$this->update(['total_records' => $total_records]);
	}

	public function setDuplicateds($duplicateds){
	    $this->update(['duplicateds' => $duplicateds]);
    }

	public function handle() {

		set_time_limit(0);
		ignore_user_abort(true);

		$class = self::TYPES[$this->type];

		if(!$class) throw new \InvalidArgumentException("No handler for import type: {$this->type}");

		$handler = new $class(); /* @var $handler Importer */

		return $handler->handle($this);

	}

	public function getAbsolutePath() {
		return storage_path('app/' . $this->path);
	}

	/**
	 * Creates a new import job from a file attachment
	 * @param string $importType The type of import job to run
	 * @param Attachment $attachment The attached file to process
	 * @return ImportJob
	 * @throws \Exception when import type is invalid
	 */
	public static function createFromAttachment(string $importType, Attachment $attachment) {

		if(!in_array($importType, array_keys(self::TYPES))) {
			throw new \Exception("Invalid import type: {$importType}");
		}

		return self::create([
			'type' => $importType,
			'status' => self::STATUS_PENDING,
			'path' => $attachment->uri,
			'user_id' => $attachment->uploader_id,
			'tenant_id' => $attachment->tenant_id
		]);

	}

	/**
	 * Fetches all jobs assigned to a tenant, optionally filtering by job type
	 * @param string $tenantID The ID of the tenant
	 * @param null|string $type [optional] The job type
	 * @return ImportJob[]|Collection
	 */
	public static function fetchTenantJobs(string $tenantID, ?string $type = null) {

		$q = self::query()
			->with(['user', 'tenant'])
			->orderBy('created_at', 'DESC')
			->where('tenant_id', $tenantID);

		if($type !== null) {
			$q->where('type', $type);
		}

		return $q->get();

	}

}