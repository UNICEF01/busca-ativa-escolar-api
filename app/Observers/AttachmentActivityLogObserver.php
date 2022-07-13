<?php
/**
 * busca-ativa-escolar-api
 * AttachmentActivityLogObserver.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 26/01/2017, 14:06
 */

namespace BuscaAtivaEscolar\Observers;


use BuscaAtivaEscolar\ActivityLog;
use BuscaAtivaEscolar\Attachment;

class AttachmentActivityLogObserver {

	public function created(Attachment $attachment) {
		ActivityLog::writeEntry($attachment, 'created', [
			'parent_type' => $attachment->content_type,
			'parent_id' => $attachment->content_id,
			'request' => request()->all()
		], ['source' => get_class()]);

		ActivityLog::writeEntry($attachment->content, 'added_attachment', [
			'target_name' => (method_exists($attachment->content, 'toString') ? $attachment->content->toString() : null),
			'mime_type' => $attachment->mime_type,
			'uri' => $attachment->uri,
			'file_name' => $attachment->file_name,
			'description' => $attachment->description,
			'location' => $attachment->location,
			'metadata' => $attachment->metadata,
		], ['source' => get_class()]);
	}

	public function updated(Attachment $attachment) {
		ActivityLog::writeEntry($attachment, 'updated', [
			'parent_type' => $attachment->content_type,
			'parent_id' => $attachment->content_id,
			'request' => request()->all()
		], ['source' => get_class()]);
	}

	public function deleted(Attachment $attachment) {
		ActivityLog::writeEntry($attachment, 'deleted', [
			'parent_type' => $attachment->content_type,
			'parent_id' => $attachment->content_id,
			'request' => request()->all()
		], ['source' => get_class()]);
	}

}