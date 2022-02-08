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
use BuscaAtivaEscolar\Transformers\GenericTransformer;
use BuscaAtivaEscolar\Transformers\GroupTransformer;

class GroupsController extends BaseController {

	public function index() {

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

    public function returnsGroupedGroups() {
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
            if(!$settings) { $settings = null; }
            else{ $group->settings = $this->transform($settings); }

            $group->children = $group->children->map(function ($group2){

                $settings = $group2->getSettings();
                if(!$settings) { $settings = null; }
                else{ $group2->settings = $this->transform($settings); }

                $group2->children = $group2->children->map(function ($group3){

                    $settings = $group3->getSettings();
                    if(!$settings) { $settings = null; }
                    else{ $group3->settings = $this->transform($settings); }

                    $group3->children = $group3->children->map(function ($group4){

                        $settings = $group4->getSettings();
                        if(!$settings) { $settings = null; }
                        else{ $group4->settings = $this->transform($settings); }

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

    public function getGroupByUser(){
        $group_id = $this->currentUser()->group_id;
        $groups_ids = Group::where('id',$group_id)
                        ->orWhere('parent_id',$group_id)
                        ->get()->toArray();
        $ids = [];
        for($i = 0; $i < count($groups_ids); ++$i){
            $ids[$i] = [$groups_ids[$i]['id'], $groups_ids[$i]['name']];
            $groups_ids2 = Group::where('id',$groups_ids[$i]['id'])
                ->orWhere('parent_id',$groups_ids[$i]['id'])
                ->get()->toArray();
            if($groups_ids2){
                for($j = 0; $j < count($groups_ids2); ++$j){
                    $ids[$i][$j] = [$groups_ids2[$j]['id'], $groups_ids2[$j]['name']];
                    $groups_ids3 = Group::where('id',$groups_ids2[$j]['id'])
                        ->orWhere('parent_id',$groups_ids2[$j]['id'])
                        ->get()->toArray();
                    if($groups_ids3){
                        for($l = 0; $l < count($groups_ids3); ++$l){
                            $ids[$i][$j][$l] = [$groups_ids3[$l]['id'], $groups_ids3[$l]['name']];
                            $groups_ids4 = Group::where('id',$groups_ids3[$l]['id'])
                                ->orWhere('parent_id',$groups_ids3[$l]['id'])
                                ->get()->toArray();
                            if($groups_ids4){
                                for($k = 0; $k < count($groups_ids3); ++$k)
                                    $ids[$i][$j][$l][$k] = [$groups_ids4[$k]['id'], $groups_ids4[$k]['name']];
                            }
                        }
                    }
                }
            }
        }
        $ids = array_map("unserialize", array_unique(array_map("serialize", $ids)));
        return response()->json(['data' => $ids]);
    }

	public function findByTenant(){

        $tenant_id = request('tenant_id');

	    $query = Group::whereHas('tenant', function($query) use ($tenant_id){
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

    public function findByUf(){

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

	public function store() {
	    $isUF = Auth::user()->isRestrictedToUF();

		$group = new Group();
		$group->fill(request()->all());
		$group->is_primary = false;

		$group->tenant_id = $isUF ? null : Auth::user()->tenant_id;
		$group->uf = $isUF ? Auth::user()->uf : null;

		$group->save();

		return response()->json(['status' => 'ok', 'group' => $group]);

	}

	public function update_settings(Group $group) {
		$settings = $group->getSettings();

		if( strtolower($group->name) == "secretaria municipal de educação" || strtolower($group->name) == "secretaria de educação" ){
            $request = request('settings', []);
            foreach ($request['alerts'] as $key => $alert){
                if($key !== 500 AND $key !== 600 AND $alert !== true){
                    return response()->json(['status' => 'error', 'message' => 'O grupo Secretaria Municipal de Educação, obrigatoriamente, deve estar selecionado para todos os motivos de evasão escolar.' ], 405);
                }
            }
		}

		$settings->update( request('settings', []) );
		$group->setSettings($settings);
        return response()->json(['status' => 'ok']);
	}

	public function update(Group $group) {
		$group->fill(request()->only(['name','parent_id']));
		$group->save();
        foreach ( $group->cases as $case) {
            $case->save();
            $case->child->save(); //reindex
        }
		return response()->json(['status' => 'ok', 'group' => $group]);
	}

	public function destroy(Group $group) {

	    $targetGroup = Auth::user()->isRestrictedToTenant()
            ? $group->tenant->primaryGroup->id // If group is tenant-bound, existing users get moved to primary group
            : null; // Else (if UF-bound), users get moved to no group at all.

        $group->users()->update(['group_id' => $targetGroup]);
		$group->delete();

		return response()->json(['status' => 'ok', 'users_moved_to' => $targetGroup]);
	}

    public function replaceAndDelete(Group $group){
        //$group -> to remove
        $groupToReceive = Group::where('id', '=', request()['replace'])->get()->first();
        $group->users()->update(['group_id' => $groupToReceive->id]);
        $group->cases()->update(['group_id' => $groupToReceive->id]);

        foreach ( $groupToReceive->cases as $case) {
            $case->save();
            $case->child->save(); //reindex
        }
        $group->delete();
        return response()->json(['status' => 'ok', 'users_and_cases_moved_to' => $groupToReceive->id]);
    }

    public function transform($data) {
        if(is_array($data)) return $data;
        if(is_object($data)) return (array) $data;
        throw new \Exception('Cannot apply generic transformation to a non-object');
    }

    public function getGroup($groupId){

        $query = $this->currentUser()->isRestrictedToUF()
            ? Group::withoutGlobalScope()->where('uf', $this->currentUser()->uf)
            : Group::query();

        $groups = $query
            ->orderBy('created_at', 'ASC')
            ->where('id', '=', $groupId)
            ->with('children.children.children')
            ->get();

        $groups = $groups->map(function ($group) {

            $settings = $group->getSettings();
            if(!$settings) { $settings = null; }
            else{ $group->settings = $this->transform($settings); }

            $group->children = $group->children->map(function ($group2){

                $settings = $group2->getSettings();
                if(!$settings) { $settings = null; }
                else{ $group2->settings = $this->transform($settings); }

                $group2->children = $group2->children->map(function ($group3){

                    $settings = $group3->getSettings();
                    if(!$settings) { $settings = null; }
                    else{ $group3->settings = $this->transform($settings); }

                    $group3->children = $group3->children->map(function ($group4){

                        $settings = $group4->getSettings();
                        if(!$settings) { $settings = null; }
                        else{ $group4->settings = $this->transform($settings); }

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
}