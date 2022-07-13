<?php

namespace BuscaAtivaEscolar\Console\Commands;

use Illuminate\Console\Command;

class fixNameChildPesquisa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fix_name_child';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edita o nome da crianca na etapa de Pesquisa para o mesmo da etapa do alerta. Apenas para IDs especificados';

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
        $child_id = $this->ask("Informe o ID da criança: ");

        $child = \BuscaAtivaEscolar\Child::where('id', $child_id)->first();

        $alerta = \BuscaAtivaEscolar\CaseSteps\Alerta::where('child_id', $child_id)->first();

        $pesquisa = \BuscaAtivaEscolar\CaseSteps\Pesquisa::where('child_id', $child_id)->first();

        $pesquisa->name = $alerta->name;

        $pesquisa->timestamps = false;

        $pesquisa->save();

        $pesquisa->timestamps = true;

        $child->name = $alerta->name;

        $child->save();

    }
}
