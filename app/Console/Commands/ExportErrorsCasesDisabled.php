<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\User;
use Excel;
use Illuminate\Console\Command;

class ExportErrorsCasesDisabled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:to_export_errors_from_user_disabled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Localiza casos bloqueados devido a desativação de usuários que estavam como responsáveis e salva os nomes na pasta storage\app\attachments\users_disabled';

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
     * @return mixed
     */
    public function handle()
    {

        $this->comment('Gerando arquivos com todos as criancas/ adolescentes que estao com casos sob a supervisao de usuario desativado');

        $states = [
            'AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES',
            'GO', 'MA', 'MG', 'MS', 'MT', 'PA', 'PB', 'PE',
            'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC',
            'SE', 'SP', 'TO'
        ];

        foreach ($states as $state){

            $this->comment('------------------------------------------------');
            $this->comment('Buscando usuários desativados do estado de '.$state);

            $disabledUsersWithTenantsArray = User::onlyTrashed()->has('tenant')->select('id', 'tenant_id')->where('work_uf', '=', $state)->get()->toArray();

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

            Excel::create('buscaativaescolar_users_'.strtolower($state), function($excel) use ($children, $disabledUsers) {
                $excel->sheet('users', function($sheet) use ($children, $disabledUsers) {

                    $sheet->appendRow(
                        [
                            'UF',
                            'MUNICIPIO',
                            'CRIANCA/ ADOLESCETE',
                            'MAE',
                            'ETAPA',
                            'ID RESPONSAVEL',
                            'NOME RESPONSAVEL',
                            'DATA DE DESATIVACAO'
                        ]
                    );

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

                            $sheet->appendRow(
                                $childFinal
                            );

                            self::$number_of_cases++;
                        }
                    }
                });
            })->store('xls', storage_path('app/attachments/users_disabled'));

            $this->comment('Salvando arquivo do estado de '.$state);

            $this->comment('Finalizando a busca dos usuários do estado de '.$state);
            $this->comment('------------------------------------------------');
            $this->comment('');

        }

        $this->comment(self::$number_of_cases." casos localizados relacionados a usuários desativados");
        $this->comment('Final do processo');

    }

    public function returnUser($id){
        return User::where('id', '=', $id)->withTrashed()->first();
    }

}
