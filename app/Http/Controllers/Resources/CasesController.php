<?php

/**
 * busca-ativa-escolar-api
 * CasesController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 30/12/2016, 16:22
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;


use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Serializers\SimpleArraySerializer;
use BuscaAtivaEscolar\Transformers\CaseTransformer;
use BuscaAtivaEscolar\User;
use Illuminate\Http\Request;

class CasesController extends BaseController
{

    public function show(ChildCase $case)
    {
        return fractal()
            ->item($case)
            ->transformWith(new CaseTransformer())
            ->serializeWith(new SimpleArraySerializer())
            ->parseIncludes(request('with'))
            ->respond();
    }

    public function cancel(ChildCase $case)
    {
        try {

            $reason = request('reason');

            if (!$reason)
                return $this->api_failure('reason_required');

            $case->cancel($reason);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $ex) {
            return response()->json(['status' => 'error', 'reason' => $ex->getMessage()]);
        }
    }

    public function reopen(ChildCase $case)
    {
        try {

            $reason = request('reason');

            if (!$reason)
                return $this->api_failure('reason_required');

            return $case->reopen($reason);
        } catch (\Exception $ex) {
            return response()->json(['status' => 'error', 'result' => $ex->getMessage()]);
        }
    }

    public function requestReopen(ChildCase $case)
    {

        try {

            $reason = request('reason');

            if (!$reason)
                return $this->api_failure('reason_required');

            return $case->requestReopen($reason);
        } catch (\Exception $ex) {
            return response()->json(['status' => 'error', 'result' => $ex->getMessage()]);
        }
    }

    public function transfer(ChildCase $case)
    {

        try {

            /* @var $user User */
            \Auth::user()->type = User::TYPE_GESTOR_NACIONAL;

            return $case->transfer();
        } catch (\Exception $ex) {
            return response()->json(['status' => 'error', 'result' => $ex->getMessage()]);
        }
    }

    public function requestTransfer(ChildCase $case)
    {

        try {

            $reason = request('reason');
            $case_id = request('case_id');
            $tenant_recipient_id = request('tenant_id');
            $city_id = request('city_id');

            if (!$reason)
                return $this->api_failure('reason_required');
            if (!$case_id)
                return $this->api_failure('case_id_required');
            if (!$tenant_recipient_id)
                return $this->api_failure('tenant_recipient_id_required');

            return $case->requestTransfer($reason, $case_id, $tenant_recipient_id, $city_id);
        } catch (\Exception $ex) {
            return response()->json(['status' => 'error', 'result' => $ex->getMessage()]);
        }
    }

    public function update(ChildCase $case)
    {
        if (request()->has('detach_user')) {
            $case->fill([
                'group_id' => request('group_id'),
                'tree_id' => implode(', ', Group::where('id', request('group_id'))->get()->first()->getTree())
            ]);
            $case->save();
            if (request('detach_user') && $case->currentStep != null) {
                $case->currentStep->detachUser();
            }
            $case->child->save(); //reindex elastic
            return response()->json(['status' => 'ok', 'case' => $case]);
        }
    }

    public function changeGroups(Request $request)
    {
        if ($request->has('newObject') and $request->has('cases')) {
            try {
                $treeIds = implode(', ', Group::where('id', $request->input('newObject')['id'])->get()->first()->getTree());

                $casesArray = array_map(function ($case) {
                    return $case['id'];
                }, $request->input('cases'));

                foreach (ChildCase::whereIn('child_id', $casesArray)->get() as $case) {
                    $currentStep = $case->currentStep;
                    $assignedUser = $currentStep->assignedUser;
                    if ($case->case_status == ChildCase::STATUS_IN_PROGRESS) {
                        if ($assignedUser != null) {
                            if (!$assignedUser->isRestrictedToUF()) {
                                if (
                                    $request->input('newObject')['id'] != $currentStep->assignedUser->group->id &&
                                    strpos($treeIds, $currentStep->assignedUser->group->id) === false
                                )
                                    $currentStep->detachUser();

                                $case->group_id = $request->input('newObject')['id'];
                                $case->tree_id = $treeIds;
                                $case->save();
                                $case->child->save(); //reindex
                            }
                        } else {
                            $case->group_id = $request->input('newObject')['id'];
                            $case->tree_id = $treeIds;
                            $case->save();
                            $case->child->save(); //reindex

                        }

                    }

                }
                return response()->json(['status' => 'ok']);
            } catch (\Exception $ex) {
                return $this->api_exception($ex);
            }
        }
    }
}