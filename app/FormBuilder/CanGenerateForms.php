<?php
/**
 * busca-ativa-escolar-api
 * GeneratesForms.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 06/04/2017, 17:01
 */

namespace BuscaAtivaEscolar\FormBuilder;

interface CanGenerateForms {

	public static function getFormFields() : FormBuilder;

}