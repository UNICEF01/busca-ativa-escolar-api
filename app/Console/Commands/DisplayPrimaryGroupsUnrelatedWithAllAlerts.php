<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Tenant;
use Illuminate\Console\Command;

class DisplayPrimaryGroupsUnrelatedWithAllAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:display_primary_groups_unrelated_with_all_alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Displays primary groups unrelated to all alerts';

    /**
     * @var array
     */
    private $alerts;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->alerts = [
            10 => 'Adolescente em conflito com a lei',
            20 => 'Criança ou adolescente com deficiência(s)',
            30 => 'Criança ou adolescente com doença(s) que impeça(m) ou dificulte(m) a frequência à escola',
            40 => 'Criança ou adolescente em abrigo',
            50 => 'Criança ou adolescente em situação de rua',
            60 => 'Criança ou adolescente vítima de abuso / violência sexual',
            61 => 'Crianças ou adolescentes migrantes estrangeiros',
            70 => 'Evasão porque sente a escola desinteressante',
            80 => 'Falta de documentação da criança ou adolescente',
            90 => 'Falta de infraestrutura escolar',
            100 => 'Falta de transporte escolar',
            110 => 'Gravidez na adolescência',
            111 => 'Infrequência escolar reportada pela gestão escolar ou pela rede de ensino"',
            120 => 'Preconceito ou discriminação racial',
            130 => 'Trabalho infantil',
            140 => 'Uso, abuso ou dependência de substâncias psicoativas',
            150 => 'Violência familiar',
            160 => 'Violência na escola',
            170 => 'Mudança de domicílio, viagem ou deslocamentos frequentes',
            180 => 'Violência no território',
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Processo de busca dos grupos iniciado');
        Tenant::where('is_setup', true)->chunk(200, function ($tenants){
            foreach ($tenants as $tenant) {

                $primary_group = $tenant->primaryGroup;
                $settings_primary_group = $primary_group->getSettings();

                foreach ($this->alerts as $key => $alert){
                    if(!$settings_primary_group->handlesAlertCause($key)){
                        $this->comment('Tenant: '.$tenant->name);
                        $this->comment('Primary group: '.$primary_group->name);
                        $this->comment('Visualiza e notifica: '.$settings_primary_group->handlesAlertCause($key) . ' - Key of cause alert: ' . $alert);
                        $this->comment("");
                    }

                }

            }
        });
        $this->comment('Processo de busca dos grupos finalizado');
    }
}
