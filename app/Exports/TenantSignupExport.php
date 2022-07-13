<?php

namespace BuscaAtivaEscolar\Exports;

use BuscaAtivaEscolar\TenantSignup;
use BuscaAtivaEscolar\City;
use Maatwebsite\Excel\Concerns\Exportable;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Str;

/**
 * @property City|null $city
 */
class TenantSignupExport implements FromQuery, ShouldAutoSize, WithHeadings
{
    use Exportable;
    public function __construct($status, $city_name, $city_uf, $created_at)
    {
        $this->status = $status;
        $this->city_name = $city_name;
        $this->city_uf = $city_uf;
        $this->created_at = $created_at;
    }

    public function query()
    {
        $city_name = $this->city_name;
        $city_uf = $this->city_uf;
        $created_at = $this->created_at;
        $status = $this->status;
        $query =  TenantSignup::query()
            ->with(['city', 'judge', 'tenant.operationalAdmin', 'tenant.politicalAdmin'])
            ->orderBy('created_at', 'ASC');
        switch ($status) {
            case "all":
                $query->withTrashed();
                break;
            case "rejected":
                $query->withTrashed()->whereNotNull('deleted_at')->where('is_approved', 0);
                break;
            case "canceled":
                $query->withTrashed()->whereNotNull('deleted_at')->where('is_approved', 1);
                break;
            case "pending_approval":
                $query->where('is_approved', 0);
                break;
            case "pending_setup":
                $query->where('is_approved', 1)->where('is_provisioned', 0);
                break;
            case "active":
                $query->where('is_approved', 1)->where('is_provisioned', 1);
                break;
            case "pending":
            default:
                break;
        }
        if (isset($this->city_name) && strlen($this->city_name) > 0) {
            $query->whereHas('city', function ($sq) use ($city_name) {
                return $sq->where('name_ascii', 'REGEXP', Str::ascii($city_name));
            });
        }

        if (isset($this->city_uf) && strlen($this->city_uf) > 0) {
            $query->whereHas('city', function ($sq) use ($city_uf) {
                return $sq->where('uf', 'REGEXP', Str::ascii($city_uf));
            });
        }

        if (isset($this->created_at) && strlen($this->created_at) > 0) {
            $numDays = intval($created_at);
            $cutoffDate = Carbon::now()->addDays(-$numDays);

            $query->where('created_at', '>=', $cutoffDate->format('Y-m-d H:i:s'));
        }

        return $query;
    }

    public function prepareRows($tenantsSignups)
    {
        return $tenantsSignups->transform(function ($tenantSignup) {
            return $tenantSignup->toExportArray();
        });
    }

    public function headings(): array
    {
        return [
            'Região',
            'UF',
            'Município',
            'Adesão',
            'Data adesão',
            'Status (Considerando últimos 30 dias)',
            'Último acesso',
            'Adesão - Gestor - Nome',
            'Adesão - Gestor - E-mail',
            'Adesão - Gestor - Telefone',
            'Adesão - Prefeito - Nome',
            'Adesão - Prefeito - E-mail',
            'Adesão - Prefeito - Telefone',
            'Instância - Gestor Operacional - Nome',
            'Instância - Gestor Operacional - E-mail',
            'Instância - Gestor Operacional - Telefone',
            'Instância - Gestor Político - Nome',
            'Instância - Gestor Político - E-mail',
            'Instância - Gestor Político - Telefone',
            'Data ativação',
            'Data exclusão/ rejeição',
            'Instância - Nome',
            'Código - IBGE',
            'Ciclo'
        ];
    }
}
