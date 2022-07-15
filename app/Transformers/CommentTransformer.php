<?php
/**
 * busca-ativa-escolar-api
 * CommentTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 19/01/2017, 14:26
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\Comment;
use BuscaAtivaEscolar\NotificationCases;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class CommentTransformer extends TransformerAbstract {

	protected $availableIncludes = [
		'author',
		'content',
		'tenant',
	];

	protected $defaultIncludes = [
		'author'
	];

	public function transform(Comment $comment) {
		$commentIdInNotificationCasesTable = NotificationCases::select('comment_id')->where('comment_id', $comment->id)->first();
		//2022-04-18 15:33:11
		$comparativeDate = Carbon::createFromFormat('Y-m-d H:i:s', '2022-07-14 14:00:00');
		return [
            'id' => $comment->id,
		    'tenant_id' => $comment->tenant_id,
			'content_type' => $comment->content_type,
			'content_id' => $comment->content_id,
			'author_id' => $comment->author_id,
			'message' => $comment->message,
			'metadata' => $comment->metadata,
			'created_at' => $comment->created_at ? $comment->created_at->toIso8601String() : null,
			'dateLower' => $comment->created_at ? $comment->created_at->gt($comparativeDate): null,
			'notification' => $commentIdInNotificationCasesTable,
		];
	}

	public function includeAuthor(Comment $comment) {
		if(!$comment->author) return null;
		return $this->item($comment->author, new UserTransformer(), false);
	}

	public function includeContent(Comment $comment) {
		switch($comment->content_type) {
			case "BuscaAtivaEscolar\\Child": return $this->item($comment->content, new ChildTransformer(), false);
			case "BuscaAtivaEscolar\\ActivityLog": return $this->item($comment->content, new LogEntryTransformer(), false);
			case "BuscaAtivaEscolar\\User": return $this->item($comment->content, new UserTransformer(), false);
			default: return null;
		}
	}

	public function includeTenant(Comment $comment) {
		if(!$comment->tenant) return null;
		return $this->item($comment->tenant, new TenantTransformer(), false);
	}

}