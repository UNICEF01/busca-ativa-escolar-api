<?php

/**
 * busca-ativa-escolar-api
 * AlertsController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 09/02/2017, 19:57
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;

use Auth;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\CaseSteps\Alerta;
use DB;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Serializers\SimpleArraySerializer;
use BuscaAtivaEscolar\Transformers\AgentAlertTransformer;
use BuscaAtivaEscolar\Transformers\PendingAlertTransformer;
use Illuminate\Database\Query\Builder;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use BuscaAtivaEscolar\ChildCase;

class AlertsController extends BaseController
{

    public function get_pending()
    {

        /** @var Builder $query */
        $query = Child::query();
        $tenant = $this->currentUser()->tenant_id;

        //get alerts
        $ids = DB::table('children_cases')->select('child_id')
            ->where('tenant_id', '=', $tenant)->whereNull('deleted_at')
            ->where('group_id', $this->currentUser()->group_id)->get()->toArray();

        $where = [];

        if (request('show_suspended') == "true")
            array_push($where, ['alert_status', '=', 'rejected']);
        else
            array_push($where, ['alert_status', '=', 'pending']);

        //filter to name
        if (!empty(request()->get('name')))
            array_push($where, ['name', 'LIKE', request('name') . '%']);

        $stdRequest = null;

        //make a filter by json filter (olnly fields from Children)
        if (!empty(request()->get('sort'))) {
            $stdRequest = json_decode(request('sort'));
            if (property_exists($stdRequest, 'name')) {
                $query->orderBy('name', $stdRequest->name);
            }
            if (property_exists($stdRequest, 'risk_level')) {
                $query->orderBy('risk_level', $stdRequest->risk_level);
            }
            if (property_exists($stdRequest, 'created_at')) {
                $query->orderBy('created_at', $stdRequest->created_at);
            }
            if (property_exists($stdRequest, 'agent')) {
                $query->orderBy('name', $stdRequest->agent);
            }
            if (property_exists($stdRequest, 'neighborhood')) {
                $query->orderBy('place_neighborhood', $stdRequest->neighborhood);
            }
            if (property_exists($stdRequest, 'city_name')) {
                $query->orderBy('place_city_name', $stdRequest->city_name);
            }
            if (property_exists($stdRequest, 'alert_cause_id')) {
                $query->orderBy('alert_cause_id', $stdRequest->alert_cause_id);
            }
            if (property_exists($stdRequest, 'group_id')) {
                $query->orderBy('group_id', $stdRequest->group_id);
            }
        }

        if (!empty(request()->get('submitter_name')) || property_exists($stdRequest, 'agent')) {
            $query->whereHas('submitter', function ($sq) use ($stdRequest) {
                if (!empty(request()->get('submitter_name'))) {
                    $sq->where('name', 'LIKE', '%' . request('submitter_name') . '%');
                }
                if (property_exists($stdRequest, 'agent')) {
                    $sq->orderBy('name', $stdRequest->agent);
                }
            });
        }

        if (!empty(request()->get('neighborhood')) || property_exists($stdRequest, 'neighborhood')) {
            $query->whereHas('alert', function ($sq) use ($stdRequest) {
                if (!empty(request()->get('neighborhood'))) {
                    $sq->where('place_neighborhood', 'like', '%' . request('neighborhood') . '%');
                }
                if (property_exists($stdRequest, 'neighborhood')) {
                    $sq->orderBy('place_neighborhood', $stdRequest->neighborhood);
                }
            });
        }

        if (!empty(request()->get('city_name')) || property_exists($stdRequest, 'city_name')) {
            $query->whereHas('alert', function ($sq) use ($stdRequest) {
                if (!empty(request()->get('city_name'))) {
                    $sq->where('place_city_name', 'like', '%' . request('city_name') . '%');
                }
                if (property_exists($stdRequest, 'city_name')) {
                    $sq->orderBy('place_city_name', $stdRequest->city_name);
                }
            });
        }

        if (!empty(request()->get('alert_cause_id'))  && request()->get('alert_cause_id') !== "" || property_exists($stdRequest, 'alert_cause_id')) {
            $query->whereHas('alert', function ($sq) use ($stdRequest) {
                if (!empty(request()->get('alert_cause_id'))) {
                    $sq->where('alert_cause_id', request('alert_cause_id'));
                }
                if (property_exists($stdRequest, 'alert_cause_id')) {
                    $sq->orderBy('alert_cause_id', $stdRequest->alert_cause_id);
                }
            });
        }

        //join children_case to filter.
        if (!empty(request()->get('group_id'))  && request()->get('group_id') !== "" || property_exists($stdRequest, 'group_id')) {
            $ids = ChildCase::select('child_id')->where('children_cases.group_id', request('group_id'))->where('tenant_id', $tenant)->get()->toArray();
            $idsChild = [];
            $i = 0;
            foreach ($ids as $id)
                $idsChild[$i++] = $id['child_id'];
            $query->where($where)->whereIn('id', $idsChild);
        } else {
            $i = 0;
            $children_ids = [];
            if (count($ids) > 0) {
                foreach ($ids as $id) {
                    $children_ids[$i++] =  $id->child_id;
                }
                $query->where($where)->whereIn('id', $children_ids);
            } else
                $query->where($where);
        }

        $max = request('max', 128);
        if ($max > 128) $max = 128;
        if ($max < 16) $max = 16;

        $paginator = $query->paginate($max);
        $collection = $paginator->getCollection();

        return fractal()
            ->collection($collection)
            ->transformWith(new PendingAlertTransformer())
            ->serializeWith(new SimpleArraySerializer())
            ->paginateWith(new IlluminatePaginatorAdapter($paginator))
            ->parseIncludes(request('with'))
            ->respond();
    }

    public function accept(Child $child)
    {
        try {

            if ($child->alert_status != 'pending') {
                return response()->json(['status' => 'failed', 'reason' => 'not_pending']);
            }

            $child->acceptAlert(request()->all());

            return response()->json(['status' => 'ok']);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    public function reject(Child $child)
    {
        try {
            if ($child->alert_status != 'pending') {
                return response()->json(['status' => 'failed', 'reason' => 'not_pending']);
            }

            $child->rejectAlert();

            return response()->json(['status' => 'ok']);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    public function get_mine()
    {
        $myAlerts = Child::with('alert')
            ->orderBy('created_at', 'DESC')
            ->where('alert_submitter_id', Auth::id())
            ->get();

        return fractal()
            ->collection($myAlerts)
            ->transformWith(new AgentAlertTransformer())
            ->serializeWith(new SimpleArraySerializer())
            ->parseIncludes(request('with'))
            ->respond();
    }


    public function edit()
    {
        try {

            $dados = request()->all();
            if (gettype($dados['id']) == 'array') {
                for ($i = 0; $i < count($dados['id']); ++$i) {
                    ChildCase::where('child_id', $dados['id'][$i])->update(['group_id' => $dados['data'][1]]);
                }
            } else if (gettype($dados['data']) == 'array') {
                ChildCase::where('child_id', $dados['id'])->update(['group_id' => $dados['data'][1]]);
            } else {
                Alerta::where('child_id', $dados['id'])->update([$dados['type'] => $dados['data']]);
            }
            return response()->json(['status' => 'ok']);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }
}
