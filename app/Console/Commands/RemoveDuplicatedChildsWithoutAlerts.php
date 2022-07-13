<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Child;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Console\Command;

class RemoveDuplicatedChildsWithoutAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:remove_duplicated_childs_without_alert_by_tenant_id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove todas as criancas duplicadas no município baseados no ID do tenant. Verifica também casos em andamento. Se não existe, cria um novo caso com base no ID do motivo informado.';

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
        $tenant_id = $this->ask("Informe o ID do tenant: ");

        $tenant = \BuscaAtivaEscolar\Tenant::where('id', $tenant_id)->first();

        $this->comment("Município de ".$tenant->name);

        $this->comment("");

        $this->comment("Localizando as crianças sem casos: (alerta e demais etapas) ...");

        $this->comment("");

        $children = \BuscaAtivaEscolar\Child::doesntHave('cases')->where('tenant_id', $tenant->id)->get();

        foreach ($children as $child){
            $this->comment("----------------------");
            $this->comment("Nome: ".$child->name);
            $this->comment("Mae: ".$child->mother_name);
            $this->comment("Pai: ".$child->father_name);
            $this->comment("Cadastro: ".$child->created_at);
            $this->comment("----------------------");

            $this->comment("");
            $this->comment("");
        }

        $numberOfChildren = \BuscaAtivaEscolar\Child::doesntHave('alert')->where('tenant_id', $tenant->id)->count();

        $this->comment("Foram encontradas ".$numberOfChildren." criancas sem casos");

        $this->comment("");

        $this->comment("Removendo duplicidades ...");

        $this->comment("");

        foreach ($children as $child){
            $this->comment("----------------------");

            $this->comment("Nome: ".$child->name);

            if( $this->repetido($child) ){
                $this->comment("Sim");
                try {
                    $child->delete();
                }catch (Missing404Exception $exception){
                    $this->comment("Não localizado no Elasticsearch");
                }
                $this->comment("Removido");
            }else{
                $this->comment("Nao" );
                $this->comment("Atualizar alerta para a crianca/ adolescente - ".$child->name );
                $data = $child->toArray();
                $data['id'] = null;
                $cause_id = $this->ask("Informe o ID do motivo do alerta: ");
                $data['alert_cause_id'] = $cause_id;
                Child::spawnFromAlertData($tenant, $child->alert_submitter_id, $data);

                try {
                    $child->delete();
                }catch (Missing404Exception $exception){
                    $this->comment("Não localizado no Elasticsearch");
                }

            }

            $this->comment("----------------------");

            $this->comment("");
        }


    }

    private function repetido($child)
    {
        $childRegistered = \BuscaAtivaEscolar\Child::where(['name' => $child->name, 'mother_name' => $child->mother_name])->count();
        if( $childRegistered > 1 ) { return true; }else{ return false; }
    }
}
