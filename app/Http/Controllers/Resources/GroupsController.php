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

class GroupsController extends BaseController {

	public function index() {

		$query = $this->currentUser()->isRestrictedToUF()
            ? Group::withoutGlobalScope()->where('uf', $this->currentUser()->uf)
            : Group::query();

		$groups = $query->with('children.children')
            ->with('children.children.children')
            ->whereDoesntHave('parent')
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
            ->with('children.children.children')
            ->whereDoesntHave('parent')
            ->get();
        return response()->json(['data' => $groups]);
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
		$group->fill(request()->only(['name']));
		$group->save();

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

    public function getGroup($id) {
        $group = Group::query()->where('id', '=', $id)->with('children.children')->get()->first();
        return response()->json(['status' => 'ok', 'group' => $group]);
    }

}