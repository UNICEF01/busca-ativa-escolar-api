<?php

/**
 * busca-ativa-escolar-api
 * TenantsController.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 28/12/2016, 21:12
 */

namespace BuscaAtivaEscolar\Http\Controllers\Tenants;


use BuscaAtivaEscolar\ActivityLog;
use BuscaAtivaEscolar\Http\Controllers\BaseController;
use BuscaAtivaEscolar\Serializers\SimpleArraySerializer;
use BuscaAtivaEscolar\TenantSignup;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\Transformers\LogEntryTransformer;
use BuscaAtivaEscolar\Transformers\TenantBasicTransformer;
use BuscaAtivaEscolar\Transformers\TenantTransformer;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use DB;
use Excel;
use Illuminate\Support\Str;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Maatwebsite\Excel\Excel as ExcelB;
use BuscaAtivaEscolar\Exports\TenantsExport;

class TenantsController extends BaseController
{
    private $excel;
    public function __construct(ExcelB $excel)
    {
        $this->excel = $excel;
    }

    public function index()
    {
        $tenants = Tenant::query()
            ->orderBy('name', 'ASC')
            ->get();

        return fractal()
            ->collection($tenants)
            ->transformWith(new TenantTransformer())
            ->serializeWith(new SimpleArraySerializer())
            ->parseIncludes('city') // Does not include user info
            ->respond();
    }

    public function getByUf()
    {
        $uf = request('uf');
        $tenants = Tenant::query()
            ->where('uf', '=', $uf)
            ->orderBy('name', 'ASC')
            ->get();

        return fractal()
            ->collection($tenants)
            ->transformWith(new TenantTransformer())
            ->serializeWith(new SimpleArraySerializer())
            ->parseIncludes('city')
            ->respond();
    }
    public function getUfWithTenant()
    {
        $uf = request('uf');
        $tenants = Tenant::query()
            ->where('uf', '=', $uf)
            ->orderBy('name', 'ASC')
            ->get();

        return fractal()
            ->collection($tenants)
            ->transformWith(new TenantBasicTransformer())
            ->respond();
    }

    public function all()
    {

        $max = intval(request('max', null));

        $filter = request('filter', []);
        $sort = request('sort', []);

        $tenants = Tenant::query()->with(['operationalAdmin', 'politicalAdmin', 'users']);
        Tenant::applySorting($tenants, $sort);

        $tenants->where('is_state', 0);

        if (isset($filter['name']) && strlen($filter['name']) > 0) {
            $tenants->where('name_ascii', 'REGEXP', strtolower(Str::ascii($filter['name'])));
        }

        if (isset($filter['uf']) && strlen($filter['uf']) > 0) {
            $tenants->where('uf', 'REGEXP', strtoupper(Str::ascii($filter['uf'])));
        }

        if (isset($filter['political_admin']) && strlen($filter['political_admin']) > 0) {
            $tenants->whereHas('politicalAdmin', function ($sq) use ($filter) {
                return $sq->where('name', 'REGEXP', $filter['political_admin']);
            });
        }

        if (isset($filter['operational_admin']) && strlen($filter['operational_admin']) > 0) {
            $tenants->whereHas('operationalAdmin', function ($sq) use ($filter) {
                return $sq->where('name', 'REGEXP', $filter['operational_admin']);
            });
        }

        if (isset($filter['last_active_at']) && strlen($filter['last_active_at']) > 0) {
            $numDays = intval($filter['last_active_at']);
            $cutoffDate = Carbon::now()->addDays(-$numDays);

            $tenants->where('last_active_at', '>=', $cutoffDate->format('Y-m-d H:i:s'));
        }

        if (isset($filter['created_at']) && strlen($filter['created_at']) > 0) {
            $numDays = intval($filter['created_at']);
            $cutoffDate = Carbon::now()->addDays(-$numDays);

            $tenants->where('created_at', '>=', $cutoffDate->format('Y-m-d H:i:s'));
        }

        if (request('show_suspended', false)) $tenants->withTrashed();

        if ($this->currentUser()->isRestrictedToUF()) {
            $tenants->where('uf', $this->currentUser()->uf);
        }

        $tenants = ($max) ? $tenants->paginate($max) : $tenants->get();

        $results = fractal()
            ->collection($tenants)
            ->transformWith(new TenantTransformer())
            ->serializeWith(new SimpleArraySerializer())
            ->parseIncludes(request('with'));

        if ($max) {
            $results->paginateWith(new IlluminatePaginatorAdapter($tenants));
        }

        return $results->respond();
    }

    public function show(Tenant $tenant)
    {
        return response()->json($tenant);
    }

    public function get_recent_activity()
    {
        $max = max(1, min(32, intval(request('max'))));
        $recentActivity = ActivityLog::fetchRecentEntries(null, $max, true);

        return fractal()
            ->collection($recentActivity)
            ->transformWith(new LogEntryTransformer())
            ->serializeWith(new SimpleArraySerializer())
            ->parseIncludes(request('with'))
            ->respond();
    }

    public function cancel(Tenant $tenant)
    {

        if (!$tenant) return $this->api_failure('invalid_tenant');
        if (!$tenant->id) return $this->api_failure('no_tenant_id');

        $users = User::query()->where('tenant_id', $tenant->id);
        $signup = TenantSignup::query()->where('tenant_id', $tenant->id);

        $users->delete();
        $signup->delete();
        $tenant->delete();

        return $this->api_success();
    }

    public function export()
    {

        $name = request('name');
        $uf = request('uf');
        $political_admin = request('political_admin');
        $operational_admin = request('operational_admin');
        $last_active_at = request('last_active_at');
        $created_at = request('created_at');
        $show_suspended = request('show_suspended');

        $query = Tenant::query()->with(['operationalAdmin', 'politicalAdmin', 'users']);

        if (isset($name) && strlen($name) > 0) {
            $query->where('name_ascii', 'REGEXP', strtolower(Str::ascii($name)));
        }

        if (isset($uf) && strlen($uf) > 0) {
            $query->where('uf', 'REGEXP', strtoupper(Str::ascii($uf)));
        }

        if (isset($political_admin) && strlen($political_admin) > 0) {
            $query->whereHas('politicalAdmin', function ($sq) use ($political_admin) {
                return $sq->where('name', 'REGEXP', $political_admin);
            });
        }

        if (isset($operational_admin) && strlen($operational_admin) > 0) {
            $query->whereHas('operationalAdmin', function ($sq) use ($operational_admin) {
                return $sq->where('name', 'REGEXP', $operational_admin);
            });
        }

        if (isset($last_active_at) && strlen($last_active_at) > 0) {
            $numDays = intval($last_active_at);
            $cutoffDate = Carbon::now()->addDays(-$numDays);
            $query->where('last_active_at', '>=', $cutoffDate->format('Y-m-d H:i:s'));
        }

        if (isset($created_at) && strlen($created_at) > 0) {
            $numDays = intval($created_at);
            $cutoffDate = Carbon::now()->addDays(-$numDays);
            $query->where('created_at', '>=', $cutoffDate->format('Y-m-d H:i:s'));
        }

        if ($show_suspended == "true") {
            $query->withTrashed();
        }

        if ($this->currentUser()->isRestrictedToUF()) {
            $query->where('uf', $this->currentUser()->uf);
        }

        $tenants = $query
            ->get()
            ->map(function ($tenant) { /* @var $tenant Tenant */
                return $tenant->toExportArray();
            })
            ->toArray();
        return $this->excel->download(new TenantsExport($tenants), 'buscaativaescolar_municipios.xls');
    }
}
