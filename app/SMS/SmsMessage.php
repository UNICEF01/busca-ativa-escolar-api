<?php
/**
 * busca-ativa-escolar-api
 * SmsMessage.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 03/03/2017, 15:58
 */

namespace BuscaAtivaEscolar\SMS;


class SmsMessage {

	public $id;
	public $number;
	public $message;
	public $shortCode;
	public $carrier;

	public function __construct($id, $number, $message, $shortcode, $carrier) {
		$this->number = $number;
		$this->message = $message;
		$this->shortcode = $shortcode;
		$this->carrier = $carrier;
	}

}