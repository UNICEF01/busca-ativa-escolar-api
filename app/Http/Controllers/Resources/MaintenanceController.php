<?php

/**
 * busca-ativa-escolar-api
 * StepsController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 08/01/2017, 01:29
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;


use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Scopes\TenantScope;
use BuscaAtivaEscolar\User;
use Illuminate\Database\Eloquent\Builder;
use BuscaAtivaEscolar\ChildCase;


class MaintenanceController extends BaseController
{

    public function assignForAdminUser($userId)
    {

        try {
            $user = User::withoutGlobalScope(TenantScope::class)->findOrFail($userId);
            if ($user->tenant_id && in_array($user->type, User::$TENANT_SCOPED_TYPES)) {
                $client = \Elasticsearch\ClientBuilder::create()->setHosts(['localhost:9200'])->build();
                $updateRequest = [
                    'conflicts' => 'proceed',
                    'index' => 'children',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'filter' => [
                                    'terms' => [
                                        '_id' => [],
                                    ],
                                ],
                            ],
                        ],
                        'script' => [
                            'inline' => "ctx._source.assigned_user_id = null; ctx._source.assigned_user_name = null; ctx._source.assigned_group_name = null"
                        ]
                    ]
                ];
                foreach (ChildCase::whereHas('currentStep', function (Builder $query) use ($user) {
                    $query->where('assigned_user_id', '=', $user->id);
                })->get() as $case) {
                    $case->currentStep->detachUser();
                    $updateRequest['body']['query']['bool']['filter']['terms']['_id'][] = $case->child->id;
                }
                $client->updateByQuery($updateRequest);
            }
            $user->lgpd = 0;
            $user->save();
            $user->delete();
            return response()->json(['status' => 'ok', 'user' => $user]);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }
}
