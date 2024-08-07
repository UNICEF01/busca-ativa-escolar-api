<?php

/**
 * busca-ativa-escolar-api
 * GroupsController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 24/01/2017, 11:52
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;


use Auth;
use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Serializers\SimpleArraySerializer;
use BuscaAtivaEscolar\Transformers\GroupTransformer;

class GroupsController extends BaseController
{

    public function index()
    {

        $query = $this->currentUser()->isRestrictedToUF()
            ? Group::withoutGlobalScope()->where('uf', $this->currentUser()->uf)
            : Group::query();

        $groups = $query
            ->with('children.children.children.children')
            ->orderBy('created_at', 'ASC')->get();

        return fractal()
            ->collection($groups)
            ->transformWith(new GroupTransformer())
            ->serializeWith(new SimpleArraySerializer())
            ->parseIncludes(request('with'))
            ->respond();
    }

    public function returnsGroupedGroups()
    {
        $query = $this->currentUser()->isRestrictedToUF()
            ? Group::withoutGlobalScope()->where('uf', $this->currentUser()->uf)
            : Group::query();
        $groups = $query
            ->orderBy('created_at', 'ASC')
            ->with('children.children.children.children')
            ->whereDoesntHave('parent')
            ->get();

        $groups = $groups->map(function ($group) {

            $settings = $group->getSettings();
            if (!$settings) {
                $settings = null;
            } else {
                $group->settings = $this->transform($settings);
            }

            $group->children = $group->children->map(function ($group2) {

                $settings = $group2->getSettings();
                if (!$settings) {
                    $settings = null;
                } else {
                    $group2->settings = $this->transform($settings);
                }

                $group2->children = $group2->children->map(function ($group3) {

                    $settings = $group3->getSettings();
                    if (!$settings) {
                        $settings = null;
                    } else {
                        $group3->settings = $this->transform($settings);
                    }

                    $group3->children = $group3->children->map(function ($group4) {

                        $settings = $group4->getSettings();
                        if (!$settings) {
                            $settings = null;
                        } else {
                            $group4->settings = $this->transform($settings);
                        }

                        return $group4;
                    });

                    return $group3;
                });

                return $group2;
            });

            return $group;
        });

        return response()->json(['data' => $groups]);
    }

    public function findPrimaryByTenant()
    {
        $tenant_id = $this->currentUser()->tenant_id;
        $query = Group::whereHas('tenant', function ($query) use ($tenant_id) {
            $query->where([
                ['id', '=', $tenant_id],
                ['is_primary', '=', 1]
            ]);
        });
        $groups = $query->orderBy('created_at', 'ASC')->get();
        return response()->json(['data' => $groups]);
    }

    public function findGroupsByParent($parentId)
    {
        $query = Group::where('parent_id', '=', $parentId);
        $groups = $query->orderBy('name', 'ASC')->get();
        return response()->json(['data' => $groups]);
    }
    public function findByTenant()
    {

        $tenant_id = request('tenant_id');

        $query = Group::whereHas('tenant', function ($query) use ($tenant_id) {
            $query->where('id', '=', $tenant_id);
        });

        $groups = $query->orderBy('created_at', 'ASC')->get();

        return fractal()
            ->collection($groups)
            ->transformWith(new GroupTransformer())
            ->serializeWith(new SimpleArraySerializer())
            ->parseIncludes(request('with'))
            ->respond();
    }

    public function findByUf()
    {

        $uf = request('uf');

        $query = Group::where('uf', '=', $uf);

        $groups = $query->orderBy('created_at', 'ASC')->get();

        return fractal()
            ->collection($groups)
            ->transformWith(new GroupTransformer())
            ->serializeWith(new SimpleArraySerializer())
            ->parseIncludes(request('with'))
            ->respond();
    }

    public function store()
    {
        $isUF = Auth::user()->isRestrictedToUF();

        $name = request('name');
        $nameFound = Group::where('name', $name)->first();

        $j = 0;
        $suggestNames = [];
        $evaluate = strrpos($name, " ");
        $sufix = substr($name, $evaluate + 1, strlen($name));
        $prefix = substr($name, 0, $evaluate + 1);

        if ($nameFound) {
            for ($i = 1; $i < 50000; ++$i) {
                if (is_numeric($sufix))
                    $nameSearch = $prefix . '' . $i;
                else
                    $nameSearch = $name . ' ' . $i;
                $nameFounded = Group::where('name', $nameSearch)->first();
                if (!$nameFounded)
                    $suggestNames[$j++] =  $nameSearch;
                if ($j == 5)
                    break;
            }
            return response()->json(['status' => 'ok', 'group' => $suggestNames]);
        }

        $group = new Group();
        $group->fill(request()->all());
        $group->is_primary = false;

        $group->tenant_id = $isUF ? null : Auth::user()->tenant_id;
        $group->uf = $isUF ? Auth::user()->uf : null;

        $group->save();

        return response()->json(['status' => 'ok', 'group' => $group]);
    }

    public function update_settings(Group $group)
    {
        $settings = $group->getSettings();

        if (strtolower($group->name) == "secretaria municipal de educação" || strtolower($group->name) == "secretaria de educação") {
            $request = request('settings', []);
            foreach ($request['alerts'] as $key => $alert) {
                if ($key !== 500 and $key !== 600 and $alert !== true) {
                    return response()->json(['status' => 'error', 'message' => 'O grupo Secretaria Municipal de Educação, obrigatoriamente, deve estar selecionado para todos os motivos de evasão escolar.'], 405);
                }
            }
        }

        $settings->update(request('settings', []));
        $group->setSettings($settings);
        return response()->json(['status' => 'ok']);
    }

    public function update(Group $group)
    {
        $group->fill(request()->only(['name', 'parent_id']));
        $name = request('name');
        $nameFound = Group::where('name', $name)->first();

        $j = 0;
        $suggestNames = [];
        $evaluate = strrpos($name, " ");
        $sufix = substr($name, $evaluate + 1, strlen($name));
        $prefix = substr($name, 0, $evaluate + 1);

        if ($nameFound) {
            for ($i = 1; $i < 50000; ++$i) {
                if (is_numeric($sufix))
                    $nameSearch = $prefix . '' . $i;
                else
                    $nameSearch = $name . ' ' . $i;
                $nameFounded = Group::where('name', $nameSearch)->first();
                if (!$nameFounded)
                    $suggestNames[$j++] =  $nameSearch;
                if ($j == 5)
                    break;
            }
            return response()->json(['status' => 'ok', 'group' => $suggestNames]);
        }

        $group->save();
        foreach ($group->cases as $case) {
            $case->save();
            $case->child->save(); //reindex
        }
        return response()->json(['status' => 'ok', 'group' => $group]);
    }

    public function destroy(Group $group)
    {

        $targetGroup = Auth::user()->isRestrictedToTenant()
            ? $group->tenant->primaryGroup->id // If group is tenant-bound, existing users get moved to primary group
            : null; // Else (if UF-bound), users get moved to no group at all.

        $group->users()->update(['group_id' => $targetGroup]);
        $group->delete();

        return response()->json(['status' => 'ok', 'users_moved_to' => $targetGroup]);
    }

    public function replaceAndDelete(Group $group)
    {
        //$group -> to remove
        $groupToReceive = Group::where('id', '=', request()['replace'])->get()->first();
        $group->users()->update(['group_id' => $groupToReceive->id]);
        $group->cases()->update(['group_id' => $groupToReceive->id]);
        $group->children()->update(['parent_id' => $groupToReceive->id]);

        foreach ($groupToReceive->cases as $case) {
            $case->child->save(); //reindex
        }
        $group->delete();
        return response()->json(['status' => 'ok', 'users_and_cases_moved_to' => $groupToReceive->id]);
    }

    public function transform($data)
    {
        if (is_array($data)) return $data;
        if (is_object($data)) return (array) $data;
        throw new \Exception('Cannot apply generic transformation to a non-object');
    }

    public function findGroupedByTenant()
    {

        $tenant_id = request('tenant_id');

        $query = Group::where('is_primary', '=', 1)
            ->whereHas('tenant', function ($query) use ($tenant_id) {
                $query->where('id', '=', $tenant_id);
            });
        $query->with('children.children.children.children');
        $groups = $query->orderBy('created_at', 'ASC')->get();

        return fractal()
            ->collection($groups)
            ->transformWith(new GroupTransformer())
            ->serializeWith(new SimpleArraySerializer())
            ->parseIncludes(request('with'))
            ->respond();
    }

    public function getGroupWithParents($groupId)
    {

        $query = $this->currentUser()->isRestrictedToUF()
            ? Group::withoutGlobalScope()->where('uf', $this->currentUser()->uf)
            : Group::query();

        $groups = $query
            ->orderBy('created_at', 'ASC')
            ->where('id', '=', $groupId)
            ->with('parent.parent.parent')
            ->get();

        return response()->json(['data' => $groups]);
    }
}
