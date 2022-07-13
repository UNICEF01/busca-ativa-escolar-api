<?php
/**
 * busca-ativa-escolar-api
 * AttachmentTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 19/01/2017, 14:31
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\Attachment;
use League\Fractal\TransformerAbstract;

class AttachmentTransformer extends TransformerAbstract {

	protected $availableIncludes = [
		'uploader',
		'content',
		'tenant',
	];

	protected $defaultIncludes = [
		'uploader'
	];

	public function transform(Attachment $attachment) {
		return [
			'id' => $attachment->id,

			'tenant_id' => $attachment->tenant_id,
			'content_type' => $attachment->content_type,
			'content_id' => $attachment->content_id,
			'uploader_id' => $attachment->uploader_id,

			'mime_type' => $attachment->mime_type,
			'mime_category' => $attachment->getMimeCategory(),

			'file_name' => $attachment->file_name,
			'description' => $attachment->description,

			'uri' => $attachment->uri,
			'url' => $attachment->getDownloadURL(),

			'location' => $attachment->location,
			'metadata' => $attachment->metadata,

			'created_at' => $attachment->created_at ? $attachment->created_at->toIso8601String() : null,
		];
	}

	public function includeUploader(Attachment $attachment) {
		if(!$attachment->uploader) return null;
		return $this->item($attachment->uploader, new UserTransformer(), false);
	}

	public function includeContent(Attachment $attachment) {
		switch($attachment->content_type) {
			case "BuscaAtivaEscolar\\Child": return $this->item($attachment->content, new ChildTransformer(), false);
			case "BuscaAtivaEscolar\\ActivityLog": return $this->item($attachment->content, new LogEntryTransformer(), false);
			case "BuscaAtivaEscolar\\User": return $this->item($attachment->content, new UserTransformer(), false);
			default: return null;
		}
	}

	public function includeTenant(Attachment $attachment) {
		if(!$attachment->tenant) return null;
		return $this->item($attachment->tenant, new TenantTransformer(), false);
	}

}