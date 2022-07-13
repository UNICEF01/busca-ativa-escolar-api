<?php
/**
 * busca-ativa-escolar-api
 * CommentActivityLogObserver.php
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
use BuscaAtivaEscolar\Comment;

class CommentActivityLogObserver {

	public function created(Comment $comment) {
		ActivityLog::writeEntry($comment, 'created', [
			'parent_type' => $comment->content_type,
			'parent_id' => $comment->content_id,
			'request' => request()->all()
		], ['source' => get_class()]);

		ActivityLog::writeEntry($comment->content, 'added_comment', [
			'target_name' => (method_exists($comment->content, 'toString') ? $comment->content->toString() : null),
			'message' => $comment->message,
			'metadata' => $comment->metadata,
		], ['source' => get_class()]);
	}

	public function updated(Comment $comment) {
		ActivityLog::writeEntry($comment, 'updated', [
			'parent_type' => $comment->content_type,
			'parent_id' => $comment->content_id,
			'request' => request()->all()
		], ['source' => get_class()]);
	}

	public function deleted(Comment $comment) {
		ActivityLog::writeEntry($comment, 'deleted', [
			'parent_type' => $comment->content_type,
			'parent_id' => $comment->content_id,
			'request' => request()->all()
		], ['source' => get_class()]);
	}

}