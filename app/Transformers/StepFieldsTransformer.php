<?php
/**
 * busca-ativa-escolar-api
 * StepFieldsTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 08/01/2017, 24:04
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\CaseSteps\CaseStep;
use BuscaAtivaEscolar\School;
use League\Fractal\TransformerAbstract;

class StepFieldsTransformer extends TransformerAbstract {

	public function transform($step) {
		$data = collect($step->stepFields)
			->mapWithKeys(function ($item) use ($step) {
				return [$item => ($step->$item ?? null)];
			})
			->toArray();

		// TODO: refactor this for clarity

		if(isset($data['place_city_id'])) {
			$data['place_city'] = [
				'id' => $data['place_city_id'],
				'uf' => $data['place_uf'],
				'name' => $data['place_city_name'],
				'full_name' => ($data['place_city_name'] . ' - ' . $data['place_uf']),
			];
		}

		if(isset($data['place_lat']) && isset($data['place_lng'])) {
			$data['place_coords'] = [
				'latitude' => $data['place_lat'],
				'longitude' => $data['place_lng'],
			];
		}

		if(isset($data['lat']) || isset($data['lng'])) {
			$data['map_center'] = $step->tenant ? $step->tenant->getCoordinates() : null;
		}

		if(isset($data['school_city_id'])) {
			$data['school_city'] = [
				'id' => $data['school_city_id'],
				'uf' => $data['school_uf'],
				'name' => $data['school_city_name'],
			];
		}

		if(isset($data['school_last_id'])) {
            $school_last =  School::find($data['school_last_id']);
            $data['school_last'] = $school_last ? $school_last->toArray(): null;
		}

		if(isset($data['school_id'])) {
            $school = School::find($data['school_id']);
            $data['school'] = $school ? $school->toArray(): null;
		}

		if(isset($step->reinsertion_date_original)) {
			$data['reinsertion_date_original'] = $step->reinsertion_date_original;
		}

		return $data;
	}

}