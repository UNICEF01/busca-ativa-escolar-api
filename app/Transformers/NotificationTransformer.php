<?php
/**
 * busca-ativa-escolar-api
 * NotificationTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 09/03/2017, 14:55
 */

namespace BuscaAtivaEscolar\Transformers;


use Illuminate\Notifications\Notification;
use League\Fractal\TransformerAbstract;

class NotificationTransformer extends TransformerAbstract {

	/**
	 * @param Notification $notification
	 * @return array
	 */
	public function transform($notification) {
		return $notification->toArray();
	}

}