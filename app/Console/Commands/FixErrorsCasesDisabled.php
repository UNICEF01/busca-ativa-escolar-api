<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\User;
use Illuminate\Console\Command;

class FixErrorsCasesDisabled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:to_fix_errors_from_user_disabled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atribui casos bloqueados devido a exclusao de um usuário a um coordenador peracional ativo';

    public static $number_of_cases = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * Comando armazena todos os IDs dos usuários que pertencem a tenants e estão desabilitados.
     * De posse desse array, o método busca todos as criancas que possuem casos.
     * O método foreach percorre essas criancas e para aquelas que possuem responsável igual a um desabilitado
     * é atribuído o primeiro coordenador registrado.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->comment('Localizando casos bloqueados devido a exclusão de usuário...');
        $this->comment('------------------------------------------------');$this->comment('');

        $disabledUsersWithTenantsArray = User::onlyTrashed()->has('tenant')->select('id', 'tenant_id')->get()->toArray();

        $disabledTenantsInUsers = array_map(function ($d){
            return $d["tenant_id"];
        }, $disabledUsersWithTenantsArray);

        $disabledUsers = array_map(function ($d){
            return $d["id"];
        }, $disabledUsersWithTenantsArray);

        $children = Child::has('cases')
            ->whereIn('tenant_id', $disabledTenantsInUsers)
            ->where('current_step_type', '<>', 'BuscaAtivaEscolar\CaseSteps\Alerta')
            ->where('child_status', '<>', 'in_school')
            ->where('child_status', '<>', 'cancelled')
            ->get();

        $this->comment('Casos bloqueados:');

        foreach ($children as $child) {

            if( in_array($child->currentStep->assigned_user_id, $disabledUsers) ){

                $assigned = $this->returnUser($child->currentStep->assigned_user_id);

                $childFinal = [
                    'UF' => $child->tenant->uf,
                    'MUNICIPIO' => $child->tenant->city->name,
                    'CRIANCA/ ADOLESCENTE' => $child->name,
                    'MAE' => $child->mother_name,
                    'ETAPA' => $child->currentStep->step_type,
                    'ID RESPONSAVEL' => $child->currentStep->assigned_user_id,
                    'NOME RESPONSAVEL' => $assigned->name,
                    'DATA DE DESATIVACAO' => $assigned->deleted_at
                ];

                $coordinator = $this->returnLastCoordintor($child->tenant_id);

                if( $coordinator != null){
                    $child->currentStep->assignToUser($coordinator);
                    $this->comment($childFinal['MUNICIPIO']." / ".$childFinal['UF']." ".$childFinal['CRIANCA/ ADOLESCENTE']. " atribuída a ".$coordinator->name);
                }else{
                    $this->comment($childFinal['MUNICIPIO']." / ".$childFinal['UF']." ".$childFinal['CRIANCA/ ADOLESCENTE']. " sem coordenador ativo.");
                }

                self::$number_of_cases++;
            }
        }


        $this->comment('Foram encontrados '.self::$number_of_cases.' bloqueados devido a exclusao de usuários.');
        $this->comment('------------------------------------------------'); $this->comment("");




        $this->comment('Finalizado');
        $this->comment('------------------------------------------------');
        $this->comment('');

    }

    public function returnUser($id){
        return User::where('id', '=', $id)->withTrashed()->first();
    }

    public function returnLastCoordintor($tenantId){
        return User::where('tenant_id', $tenantId)->where('type', 'coordenador_operacional')->withoutTrashed()->orderBy('created_at', 'ASC')->first();
    }
}
