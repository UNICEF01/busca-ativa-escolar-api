<?php

/**
 * busca-ativa-escolar-api
 * UsersController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 18/01/2017, 18:36
 */

namespace BuscaAtivaEscolar\Http\Controllers\Resources;

use Auth;
use BuscaAtivaEscolar\CaseSteps\AnaliseTecnica;
use BuscaAtivaEscolar\CaseSteps\GestaoDoCaso;
use BuscaAtivaEscolar\CaseSteps\Observacao;
use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\CaseSteps\Rematricula;
use BuscaAtivaEscolar\ChildCase;
use BuscaAtivaEscolar\Exports\UsersExport;
use BuscaAtivaEscolar\ExportUsersJob;
use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Mail\UserRegisterNotification;
use BuscaAtivaEscolar\Serializers\SimpleArraySerializer;
use BuscaAtivaEscolar\Transformers\UserTransformer;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use Exception;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Mail;
use Maatwebsite\Excel\Excel as ExcelB;
use BuscaAtivaEscolar\LGPD\Interfaces\ILgpd;
use BuscaAtivaEscolar\Groups\GroupService;

class UsersController extends BaseController
{
    private $excel;
    protected $lgpdService;
    public function __construct(ExcelB $excel, ILgpd $lgpdService)
    {
        $this->excel = $excel;
        $this->lgpdService = $lgpdService;
    }

    public function search()
    {
        $query = User::with('group');

        // If user is global user, they can filter by tenant_id
        if ($this->currentUser()
            ->isGlobal() && !empty(request()
                ->get('tenant_id'))
        ) {
            $query->where('tenant_id', request('tenant_id'));
        } else if ($this->currentUser()
            ->isRestrictedToTenant()
        ) {
            $query->where('tenant_id', '!=', 'global');
        }

        // If user is global user, they can filter by UF
        if ($this->currentUser()
            ->isGlobal() && !empty(request()
                ->get('uf'))
        ) {
            $query->where('uf', request('uf'));
        } else if ($this->currentUser()
            ->isRestrictedToUF()
        ) { // Else, check if they're bound to a UF
            $query->where('uf', $this->currentUser()
                ->uf);
            if (in_array($this->currentUser()->type, User::$TYPES_VISITANTES_UFS)) {
                $query->whereIn('type', User::$UF_VISITANTES_SCOPED_TYPES);
            } else {
                $query->whereIn('type', User::$UF_SCOPED_TYPES);
            }
        }

        if ($this->currentUser()
            ->isRestrictedToTenant()
        ) {
            if (!empty(request()
                ->get('group_id')) && request()
                ->get('tree') == 0
            ) $query->where('group_id', request('group_id'));
            if (!empty(request()
                ->get('group_id')) && request()
                ->get('tree') == 1
            ) $query->where('tree_id', 'Like', '%' . request('group_id') . '%');
            if (empty(request()
                ->get('group_id')) && request()
                ->get('tree') == 1
            ) $query->where('tree_id', 'Like', '%' . $this->currentUser()->group_id . '%');
            if (empty(request()
                ->get('group_id')) && request()
                ->get('tree') == 0
            ) $query->where('group_id', \Auth::user()->group_id);
        }
        //filter for visitantes nacionais e estaduais
        if (!empty(request()
            ->get('type'))) {
            if (request('type') == User::TYPE_VISITANTE_NACIONAL) {
                $query->where('type', '=', USER::TYPE_VISITANTE_NACIONAL_UM);
                $query->orWhere('type', '=', USER::TYPE_VISITANTE_NACIONAL_DOIS);
                $query->orWhere('type', '=', USER::TYPE_VISITANTE_NACIONAL_TRES);
                $query->orWhere('type', '=', USER::TYPE_VISITANTE_NACIONAL_QUATRO);
            } elseif (request('type') == User::TYPE_VISITANTE_ESTADUAL) {
                $query->where('type', '=', USER::TYPE_VISITANTE_ESTADUAL_UM);
                $query->orWhere('type', '=', USER::TYPE_VISITANTE_ESTADUAL_DOIS);
                $query->orWhere('type', '=', USER::TYPE_VISITANTE_ESTADUAL_TRES);
                $query->orWhere('type', '=', USER::TYPE_VISITANTE_ESTADUAL_QUATRO);
            } else {
                $query->where('type', request('type'));
            }
        }

        if (!empty(request()
            ->get('email'))) $query->where('email', 'LIKE', request('email') . '%');

        if (request('show_suspended', false)) $query->withTrashed();

        if (!empty(request()
            ->get('sort'))) {
            User::applySorting($query, request('sort', []));
        }

        $max = request('max', 128);
        if ($max > 128) $max = 128;
        if ($max < 16) $max = 16;

        $paginator = $query->paginate($max);
        $collection = $paginator->getCollection();

        return fractal()
            ->collection($collection)->transformWith(new UserTransformer('short'))
            ->serializeWith(new SimpleArraySerializer())
            ->paginateWith(new IlluminatePaginatorAdapter($paginator))->parseIncludes(request('with'))
            ->respond();
    }

    public function export()
    {

        $uf = request('uf');
        $tenant_id = request('tenant_id');
        $type = request('type');
        $email = request('email');
        $show_suspended = request('show_suspended');
        $group_id = request('group_id');

        $query = User::with('group');

        if ($this->currentUser()
            ->isGlobal() && isset($tenant_id) && $tenant_id != null
        ) {
            $query->where('tenant_id', $tenant_id);
        } else if ($this->currentUser()
            ->isRestrictedToTenant()
        ) {
            $query->where('tenant_id', '!=', 'global');
        }

        if ($this->currentUser()
            ->isGlobal() && isset($uf) && $uf != null
        ) {
            $query->where('uf', $uf);
        } else if ($this->currentUser()
            ->isRestrictedToUF()
        ) {
            $query->where('uf', $this->currentUser()
                ->uf);

            if (in_array($this->currentUser()->type, User::$TYPES_VISITANTES_UFS)) {
                $query->whereIn('type', User::$UF_VISITANTES_SCOPED_TYPES);
            } else {
                $query->whereIn('type', User::$UF_SCOPED_TYPES);
            }
        }

        if (isset($group_id) && $group_id != null) $query->where('group_id', $group_id);
        if (isset($type) && $type != null) $query->where('type', $type);
        if (isset($email) && $email != null) $query->where('email0', 'LIKE', $email . '%');

        if ($show_suspended == "true") $query->withTrashed();
        $users = $query->get()->map(function ($user) {
            return $user->toExportArray();
        })
            ->toArray();

        return $this
            ->excel
            ->download(new UsersExport($users), 'buscaativaescolar_users.xls');
    }

    public function show(User $user)
    {

        return fractal()->item($user)->transformWith(new UserTransformer('long'))
            ->serializeWith(new SimpleArraySerializer())
            ->parseIncludes(request('with'))
            ->respond();
    }

    public function update(User $user)
    {
        try {

            // Here we check if we have enough permission to edit the target user
            if (!Auth::user()->canManageUser($user)) {
                return $this->api_failure('not_enough_permissions');
            }

            $input = request()->all();

            // If user is editing himself, we clear the e-mail so we avoid hitting validation rules (issue #201, #203)
            // Note: this happens due to user details confirmation flow in tenant setup
            if ($input['email'] === $user->email) {
                unset($input['email']);
            }

            // Tenant-bound users can ony manage users within their tenant
            if (Auth::user()->isRestrictedToTenant()) {
                $input['tenant_id'] = Auth::user()->tenant_id;
            }

            // UF-bound users can only manage users within their UF
            if (Auth::user()
                ->isRestrictedToUF()
            ) {
                $input['uf'] = Auth::user()->uf;
            }

            // These flags are used for validation (eg: non-tenant-bound users do not require a tenant_id, and so on)
            $isTenantUser = in_array($input['type'] ?? '', User::$TENANT_SCOPED_TYPES);
            $isUFUser = in_array($input['type'] ?? '', User::$UF_SCOPED_TYPES);

            if (isset($input['email']) && User::checkIfExists($input['email'])) {
                return $this->api_failure('email_already_exists');
            }

            $validation = $user->validate($input, false, $isTenantUser, $isUFUser);

            if ($validation->fails()) {
                return $this->api_validation_failed('validation_failed', $validation);
            }

            if (isset($input['password'])) {
                $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
            }
            if ($user->tenant_id && in_array($user->type, User::$TENANT_SCOPED_TYPES)) {
                $userAssingedCase = new GroupService;
                $userAssingedCase = $userAssingedCase->changeAssignedUserCaseData(['user_id' => $input['id'], 'name' => $input['name'], 'group_id' => $input['group_id'], 'group_name' => $input['group_name']]);

            }
            $input['tree_id'] = implode(', ', Group::where('id', $input['group_id'])->get()->first()->getTree());
            $user->fill($input);

            // Block setting a tenant-scope user without a tenant ID set
            if (!$user->tenant_id && in_array($user->type, User::$TENANT_SCOPED_TYPES)) {
                throw new Exception("tenant_id_inconsistency");
            }

            $user->save();

            // Refresh user UF (used for filtering) (maybe parent tenant changed?)
            if (!$user->uf && $user->tenant_id) {
                $user->uf = $user->tenant->uf;
                $user->save();
            }

            return response()
                ->json(['status' => 'ok', 'updated' => $input]);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    public function store()
    {
        try {

            $user = new User();
            $input = request()->all();

            // Tenant-bound users can ony manage users within their tenant
            if (Auth::user()
                ->isRestrictedToTenant()
            ) {
                $input['tenant_id'] = Auth::user()->tenant_id;
                $input['tree_id'] =  implode(', ', Group::where('id', $input['group_id'])->get()->first()->getTree());
            }

            // UF-bound users can only manage users within their UF
            if (Auth::user()
                ->isRestrictedToUF()
            ) {
                $input['uf'] = Auth::user()->uf;
            }

            // These flags are used for validation (eg: non-tenant-bound users do not require a tenant_id, and so on)
            $isTenantUser = in_array($input['type'] ?? '', User::$TENANT_SCOPED_TYPES);
            $isUFUser = in_array($input['type'] ?? '', User::$UF_SCOPED_TYPES);

            $validation = $user->validate($input, true, $isTenantUser, $isUFUser);

            if ($validation->fails()) {
                return $this->api_validation_failed('validation_failed', $validation);
            }

            $email = trim(strtolower($input['email'] ?? ''));

            if (User::checkIfExists($email)) {
                return $this->api_failure('email_already_exists');
            }

            // Cache initial password so we can send it as cleartext through e-mail later
            $length = 10;
            $input['password'] = password_hash(substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$&', ceil($length / strlen($x)))), 1, $length), PASSWORD_DEFAULT);

            $user->fill($input);

            // Block setting a tenant-scope user without a tenant ID set
            if (!$user->tenant_id && in_array($user->type, User::$TENANT_SCOPED_TYPES)) {
                throw new Exception("tenant_id_inconsistency");
            }

            // Check if the resulting user can be created by the current user
            if (!Auth::user()->canManageUser($user)) {
                return $this->api_failure('not_enough_permissions');
            }

            $user->lgpd = 0;

            $user->save();

            // Refresh user UF (used for filtering) (maybe parent tenant changed?)
            if (!$user->uf && $user->tenant_id) {
                $user->uf = $user
                    ->tenant->uf;
                $user->save();
            }

            Mail::to($user->email)
                ->send(new UserRegisterNotification($user, UserRegisterNotification::TYPE_REGISTER_INITIAL));

            return response()->json(['status' => 'ok', 'id' => $user->id]);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    public function destroy(User $user)
    {

        if (!Auth::user()->canManageUser($user)) {
            return $this->api_failure('not_enough_permissions');
        }

        if ($this->checkphases($user)) {
            return response()->json($this->checkphases($user));
        }

        try {
            $user->lgpd = 0;
            $user->save();
            $user->delete();
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    private function checkphases($user)
    {
        $checkPhases = new \stdClass();
        $checkPhases->pesquisa = Pesquisa::checkIfExistsUserWithCasesInOpem($user->id);
        $checkPhases->analise_tecnica = AnaliseTecnica::checkIfExistsUserWithCasesInOpem($user->id);
        $checkPhases->gestao_caso = GestaoDoCaso::checkIfExistsUserWithCasesInOpem($user->id);
        $checkPhases->rematricula = Rematricula::checkIfExistsUserWithCasesInOpem($user->id);
        $checkPhases->observacao = Observacao::checkIfExistsUserWithCasesInOpem($user->id);
        foreach ($checkPhases as $phase) {
            if ($phase->casos > 0) {
                $checkPhases->have_data = true;
                return $checkPhases;
            }
        }
        return false;
    }

    public function restore($user_id)
    {
        try {
            $user = User::withTrashed()->findOrFail($user_id);

            if (User::checkIfExists($user->email)) {
                return $this->api_failure('email_already_exists');
            }

            if (!Auth::user()
                ->canManageUser($user)) {
                return $this->api_failure('not_enough_permissions');
            }

            $user->restore();

            Mail::to($user->email)
                ->send(new UserRegisterNotification($user, UserRegisterNotification::TYPE_REGISTER_REACTIVATION));
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    public function reports()
    {
        $reports = \Storage::allFiles('attachments/user_reports');
        $finalReports = array_map(
            function ($file) {
                return ['file' => str_replace("attachments/user_reports/", "", $file), 'size' => \Storage::size($file), 'last_modification' => \Storage::lastModified($file)];
            },
            $reports
        );
        return response()->json(['status' => 'ok', 'data' => $finalReports]);
    }

    public function getReport()
    {
        $nameFile = request('file');
        if (!isset($nameFile)) {
            return response()->json(['error' => 'Not authorized.'], 403);
        }
        $exists = \Storage::exists("attachments/user_reports/" . $nameFile);
        if ($exists) {
            return response()->download(storage_path("app/attachments/user_reports/" . $nameFile));
        } else {
            return response()->json(['error' => 'Arquivo inexistente.'], 403);
        }
    }

    public function createReport()
    {

        dispatch((new ExportUsersJob())
            ->onQueue('export_users'));

        return response()
            ->json(['msg' => 'Arquivo criado', 'date' => Carbon::now()->timestamp], 200);
    }

    public function update_yourself(User $user)
    {

        try {

            $input = request()->all();

            // If user is editing himself, we clear the e-mail so we avoid hitting validation rules (issue #201, #203)
            // Note: this happens due to user details confirmation flow in tenant setup
            if ($input['email'] === $user->email) {
                unset($input['email']);
            }

            // Tenant-bound users can ony manage users within their tenant
            if (Auth::user()->isRestrictedToTenant()) {
                $input['tenant_id'] = Auth::user()->tenant_id;
            }

            // UF-bound users can only manage users within their UF
            if (Auth::user()
                ->isRestrictedToUF()
            ) {
                $input['uf'] = Auth::user()->uf;
            }

            // These flags are used for validation (eg: non-tenant-bound users do not require a tenant_id, and so on)
            $isTenantUser = in_array($input['type'] ?? '', User::$TENANT_SCOPED_TYPES);
            $isUFUser = in_array($input['type'] ?? '', User::$UF_SCOPED_TYPES);

            if (isset($input['email']) && User::checkIfExists($input['email'])) {
                return $this->api_failure('email_already_exists');
            }

            $validation = $user->validate($input, false, $isTenantUser, $isUFUser);

            if ($validation->fails()) {
                return $this->api_validation_failed('validation_failed', $validation);
            }

            if (isset($input['password'])) {
                $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
            }

            $user->fill($input);

            // Block setting a tenant-scope user without a tenant ID set
            if (!$user->tenant_id && in_array($user->type, User::$TENANT_SCOPED_TYPES)) {
                throw new Exception("tenant_id_inconsistency");
            }

            //LGPD
            $this
                ->lgpdService
                ->saveLgpd(['plataform_id' => $user->id, 'name' => $user->name, 'ip_addr' => request()
                    ->ip()]);

            $user->save();

            // Refresh user UF (used for filtering) (maybe parent tenant changed?)
            if (!$user->uf && $user->tenant_id) {
                $user->uf = $user
                    ->tenant->uf;
                $user->save();
            }

            return response()
                ->json(['status' => 'ok', 'updated' => $input]);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }

    public function send_reactivation_mail($user_id)
    {
        try {

            $user = User::findOrFail($user_id);
            Mail::to($user->email)
                ->send(new UserRegisterNotification($user, UserRegisterNotification::TYPE_REGISTER_REACTIVATION));
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }
}
