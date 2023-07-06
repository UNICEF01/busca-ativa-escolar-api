<?php
/**
 * busca-ativa-escolar-api
 * SchoolImportController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 05/04/2017, 16:38
 */

namespace BuscaAtivaEscolar\Http\Controllers\Maintenance;


use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\ImportJob;

class ImportController extends BaseController {

	public function index() {
        $page = request('page');
        $per_page = request('per_page');
		$jobs = ImportJob::with('tenant')->orderBy('created_at','DESC')->paginate(intval($per_page), ['*'], 'page', intval($page));

		return $jobs;
	}

	public function upload_file() {
				
		$file = request()->file('file');
		$type = request('type');

        if(!in_array($type, array_keys(ImportJob::TYPES))) {
			return $this->api_failure('invalid_type');
		}

		$key = uniqid($type . '_', true);
		$extension = trim(strtolower($file->getClientOriginalExtension()));

		$path = $file->storeAs('imported', "{$key}.{$extension}");

		$job = ImportJob::create([
			'type' => $type,
			'path' => $path,
		]);

		return $this->api_success(['job_id' => $job]);

	}

	public function get_job(ImportJob $job) {
		return response()->json(['data' => $job]);
	}

	public function process_job(ImportJob $job) {

		if($job->status === ImportJob::STATUS_COMPLETED) {
			return $this->api_failure('job_already_completed');
		}

		try {

			$job->setStatus(ImportJob::STATUS_PROCESSING);

			$job->handle();
			
			$job->setStatus(ImportJob::STATUS_COMPLETED);

		} catch (\Exception $ex) {

			$job->setStatus(ImportJob::STATUS_FAILED);

			$job->storeError($ex);
			$job->save();

			return $this->api_exception($ex);

		}

		return $this->api_success(['job_id' => $job->id]);

	}

}