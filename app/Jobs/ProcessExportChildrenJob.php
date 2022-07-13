<?php

namespace BuscaAtivaEscolar\Jobs;

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Rap2hpoutre\FastExcel\FastExcel;
use BuscaAtivaEscolar\Search\Search;

class ProcessExportChildrenJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $paramsQuery;

    public function __construct($user, $paramsQuery)
    {
        $this->user = $user;
        $this->paramsQuery = $paramsQuery;
    }

    public function handle(Search $search)
    {

        set_time_limit(0);
        Log::info("Iniciando processo de exportacao das criancas do municipio");
        File::makeDirectory(storage_path("app/attachments/children_reports/" . $this->user->id), $mode = 0777, true, true);
        (new FastExcel($this->childrenGenerator($search)))->export(storage_path("app/attachments/children_reports/" . $this->user->id . "/" . $this->user->id . ".xlsx"));

        Log::info("Finalizando processo de exportacao das criancas do municipio");
    }

    function childrenGenerator(Search $search)
    {
        $results = $search->search(new Child(), $this->paramsQuery, 10000);

        foreach ($results['hits']['hits'] as $child) {
            yield $this->transformChildToArrayExport((object) $child['_source']);
        }
    }

    function transformChildToArrayExport($child)
    {
        return [
            'Nome' => $child->name ?? '',
            'Nome da mãe' => $child->mother_name ?? null,
            'Nome do pai' => $child->father_name ?? null,
            'Risco' => isset($child->risk_level) ? trans('risk_level.' . $child->risk_level) : null,
            'Sexo' => isset($child->gender) ? trans('child.gender.' . $child->gender) : null,
            'Idade' => $child->age,
            'Usuário responsável' => $child->assigned_user_name ?? null,
            'Etapa' => $child->step_name ?? null,
            'Está Atrasado?' => (($child->deadline_status ?? 'normal') === Child::DEADLINE_STATUS_LATE) ? 'Sim' : 'Não',
            'Status da Criança' => trans('child.status.' . $child->child_status ?? null),
            'Status do Caso' => trans('child.status.' . $child->child_status ?? null),
            'Status do Prazo' => trans('child.deadline_status.' . $child->deadline_status ?? null),
            'Status do Alerta' => trans('child.alert_status.' . $child->alert_status ?? null),
            'Data Criado' => isset($child->created_at) ? Carbon::createFromTimestamp(strtotime($child->created_at))->toIso8601String() : null,
            'Data Atualizado' => isset($child->updated_at) ? Carbon::createFromTimestamp(strtotime($child->updated_at))->toIso8601String() : null,
            'Endereco' => $child->place_address ?? '',
            'Bairro' => $child->place_neighborhood ?? '',
            'Referencia' => $child->place_reference ?? '',
            'CEP' => $child->place_cep ?? '',
        ];
    }
}
