<?php
/**
 * busca-ativa-escolar-api
 * ValidationException.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 08/03/2017, 14:52
 */

namespace BuscaAtivaEscolar\Exceptions;


use Illuminate\Contracts\Validation\Validator;

class ValidationException extends \Exception {

	protected $reason = null;
	protected $validator = [];

	public function __construct($reason, Validator $validator = null) {
		$this->reason = $reason;
		$this->validator = $validator;
		parent::__construct($reason, 100, null);
	}

	public function getReason() {
		return $this->reason;
	}

	public function getValidator() {
		return $this->validator;
	}

}