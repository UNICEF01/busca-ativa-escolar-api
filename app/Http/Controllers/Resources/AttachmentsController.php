<?php
/**
 * busca-ativa-escolar-api
 * AttachmentsController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 31/01/2017, 15:55
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;


use BuscaAtivaEscolar\Attachment;
use BuscaAtivaEscolar\Http\Controllers\BaseController;

class AttachmentsController extends BaseController {

	public function download(Attachment $attachment) {
		return response()->download($attachment->getFile(), basename($attachment->file_name));
	}

}