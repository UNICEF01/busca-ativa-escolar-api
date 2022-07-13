<?php
/**
 * busca-ativa-escolar-api
 * Attachment.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 19/01/2017, 13:36
 */

namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use BuscaAtivaEscolar\Traits\Data\TenantScopedModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Webpatser\Uuid\Uuid;

/**
 * @property int $id
 *
 * @property string $tenant_id
 * @property string $content_type
 * @property string $content_id
 * @property string $uploader_id
 * @property string $mime_type
 * @property string $file_name
 * @property string $description
 * @property string $uri
 * @property string $location
 * @property object $metadata
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property Tenant $tenant
 * @property User $uploader
 * @property Model $content
 */
class Attachment extends Model {

	use SoftDeletes;
	use IndexedByUUID;
	use TenantScopedModel;

	protected $table = "attachments";
	protected $fillable = [
		'tenant_id',
		'content_type',
		'content_id',
		'uploader_id',
		'mime_type',
		'file_name',
		'description',
		'uri',
		'location',
		'metadata',
	];

	protected $casts = [
		'metadata' => 'object'
	];

	// -----------------------------------------------------------------------------------------------------------------

	/**
	 * The tenant that owns this attachment. May be null if the content object is not tenant-scoped.
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function tenant() {
		return $this->hasOne('BuscaAtivaEscolar\Tenant', 'id', 'tenant_id');
	}

	/**
	 * The user who uploaded this attachment. May be null if the attachment was created by a command/bot/etc.
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function uploader() {
		return $this->hasOne('BuscaAtivaEscolar\User', 'id', 'uploader_id');
	}

	/**
	 * The content this attachment is attached to. Is an instance of Eloquent's Model. May or may not be tenant-scoped.
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

	public function getURL() {
		// TODO: implement storage domain / CDN / S3
		switch($this->location) {
			case "local":default: return url($this->uri);
		}
	}

    public function getURLPublic() {
        return str_replace("public", "storage", url($this->uri));
    }

	public function getFile() {
		switch($this->location) {
			case "local":default: return storage_path("app/{$this->uri}");
		}
	}

	public function getDownloadURL() {
		return url(route('api.attachments.download', [$this->id]));
	}

	public function getMimeCategory() {
		if(!$this->mime_type) return 'application';
		return substr($this->mime_type, 0, strpos($this->mime_type, '/'));
	}

	public function getBasePath() {
		return self::generateBasePath($this->content_type, $this->content_id);
	}

	// -----------------------------------------------------------------------------------------------------------------

	/**
	 * Creates an attachment through a file uploaded via HTTP. Used for Educacenso and XLS of municipalities
	 *
	 * @param UploadedFile $upload The uploaded file
	 * @param Model $content The content entity that this attachment should be attached to
	 * @param User $uploader The user who uploaded the file (or null if uploaded by command/bot/etc.)
	 * @param string $description The description of the file, as given by the uploader
	 * @return Attachment
	 * @throws \Exception
	 */
	public static function createFromUpload(UploadedFile $upload, Model $content, $uploader = null, $description = '') {
		$attachment = new Attachment();
		$attachment->id = Uuid::generate()->string;

		$attachment->tenant_id = $content->tenant_id ?? null;
		$attachment->content_type = get_class($content);
		$attachment->content_id = $content->id;
		$attachment->uploader_id = $uploader->id ?? null;

		$attachment->mime_type = $upload->getMimeType();
		$attachment->location = "local";
		$attachment->file_name = $upload->getClientOriginalName();

		$attachment->metadata = $upload;
		$attachment->description = $description;

		$fileName = $attachment->id . "." . strtolower(basename($upload->getClientOriginalExtension()));
		$attachment->uri = $upload->storeAs($attachment->getBasePath(), $fileName);

		$attachment->save();

		return $attachment;

	}

    /**
     * Cria um arquivo baseado na imagem carregada no momento da adesão de um município
     *
     * @param UploadedFile $upload The uploaded file
     * @return Attachment
     * @throws \Exception
     */
    public static function createFromImageTituloEleitor(UploadedFile $upload, $description = '') {
        $attachment = new Attachment();
        $attachment->id = Uuid::generate()->string;

        $attachment->tenant_id = null;
        $attachment->content_type = get_class($attachment);
        $attachment->content_id = $attachment->id;
        $attachment->uploader_id = null;

        $attachment->mime_type = $upload->getMimeType();
        $attachment->location = "local";
        $attachment->file_name = $upload->getClientOriginalName();

        $attachment->metadata = $upload;
        $attachment->description = $description;

        $fileName = $attachment->id . "." . strtolower(basename($upload->getClientOriginalExtension()));
        $attachment->uri = $upload->storeAs("public/signups", $fileName);

        $attachment->save();

        return $attachment;

    }

	/**
	 * Generates the base (directory) path based on a specific content type/id
	 * @param string $content_type The content type
	 * @param string $content_id The content ID
	 * @return string The directory path
	 */
	public static function generateBasePath($content_type, $content_id) {
		$content_type = strtolower(str_replace(['/','\\'],'_', $content_type));
		return "attachments/{$content_type}/{$content_id}";
	}

}