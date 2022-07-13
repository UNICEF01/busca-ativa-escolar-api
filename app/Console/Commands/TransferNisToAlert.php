<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use Illuminate\Console\Command;

class TransferNisToAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atualizacao:transfer_nis_to_alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfere as informacoes do NIS da etapa de Pesquisa para a etapa de Alerta';

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

        $this->comment("INICIANDO A CORRECAO DO NIS EXISTENTE EM PESQUISA E NAO LOCALIZADO NA ETAPA ALERTA");
        Pesquisa::chunk(200, function ($pesquisas){
            foreach ($pesquisas as $pesquisa){

                $alerta = Alerta::where('child_id', $pesquisa->child_id)->first();

                $this->comment('ATUALIZANDO O NIS DO ALERTA DA CRIANCA '.$alerta->name);

                if($pesquisa->nis != $alerta->nis){

                    $this->comment("DIFERENTES: ");
                    $this->comment("ALERTA NOME: ".$alerta->name);
                    $this->comment("PESQUISA NOME: ".$pesquisa->name);

                    $this->comment("ALERTA NIS: ".$alerta->nis);
                    $this->comment("PESQUISA NIS: ".$pesquisa->nis);

                    $this->comment("");
                    $this->comment("-----------------------");

                    $pesquisa->nis = $alerta->nis;

                    $pesquisa->save();

                }
            }
        });
        $this->comment("FINALIZANDO A CORRECAO DO NIS");

    }
}
