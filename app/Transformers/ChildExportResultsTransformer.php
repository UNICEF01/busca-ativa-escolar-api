<?php
/**
 * busca-ativa-escolar-api
 * ChildSearchResultsTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2018
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 15/01/2018, 17:30
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\Child;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ChildExportResultsTransformer extends TransformerAbstract {

	public function transform($document) {

		if(!isset($document['_source'])) {
			return [];
		}

		return [
			//'ID' => $document['_id'],
			'Nome' => $document['_source']['name'] ?? '',

			'Nome da mãe' => $document['_source']['mother_name'] ?? null,
			'Nome do pai' => $document['_source']['father_name'] ?? null,

			'Risco' => isset($document['_source']['risk_level']) ? trans('risk_level.' . $document['_source']['risk_level']) : null,
			'Sexo' => isset($document['_source']['gender']) ? trans('child.gender.' . $document['_source']['gender']) : null,
			'Idade' => $document['_source']['age'],

			'Usuário responsável' => $document['_source']['assigned_user_name'] ?? null,

			//'ID do Caso' => $document['_source']['current_case_id'] ?? null,

			'Etapa' => $document['_source']['step_name'] ?? null,

			'Está Atrasado?' => (($document['_source']['deadline_status'] ?? 'normal') === Child::DEADLINE_STATUS_LATE) ? 'Sim' : 'Não',

			'Status da Criança' => trans('child.status.' . $document['_source']['child_status'] ?? null),
			'Status do Caso' => trans('child_case.status.' . $document['_source']['case_status'] ?? null),
			'Status do Prazo' => trans('child.deadline_status.' . $document['_source']['deadline_status'] ?? null),
			'Status do Alerta' => trans('child.alert_status.' . $document['_source']['alert_status'] ?? null),

			'Data Criado' => isset($document['_source']['created_at']) ? Carbon::createFromTimestamp(strtotime($document['_source']['created_at']))->toIso8601String() : null,
			'Data Atualizado' => isset($document['_source']['updated_at']) ? Carbon::createFromTimestamp(strtotime($document['_source']['updated_at']))->toIso8601String() : null,

            'Endereco' => $document['_source']['place_address'] ?? '',
            'Bairro' => $document['_source']['place_neighborhood'] ?? '',
            'Referencia' => $document['_source']['place_reference'] ?? '',
            'CEP' => $document['_source']['place_cep'] ?? '',
            'Escola de rematricula' => $document['_source']['school_name'] ?? '',
		];
	}

}