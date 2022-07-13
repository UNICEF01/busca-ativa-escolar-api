<?php
/**
 * busca-ativa-escolar-api
 * FormBuilderController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 06/04/2017, 17:48
 */

namespace BuscaAtivaEscolar\Http\Controllers\Integration;


use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\Http\Controllers\BaseController;

class FormBuilderController extends BaseController {

	public function render_form($formName) {

		// TODO: add caching

		switch($formName) {
			case "pesquisa": $form = Pesquisa::getFormFields(); break;
			case "alerta": $form = Alerta::getFormFields(); break;
			default: return response()->json(['status' => 'error', 'reason' => 'invalid_form_name']);
		}

		return response()->json(['status' => 'ok', 'form' => $form->buildTree()]);

	}

}