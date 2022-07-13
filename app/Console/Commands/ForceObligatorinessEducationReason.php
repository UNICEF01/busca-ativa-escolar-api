<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Tenant;
use Illuminate\Console\Command;

class ForceObligatorinessEducationReason extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:force_obligatoriness_education_reason';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the all reason with Secretaria Municipal de Educação default';

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
            10 => true, // Adolescente em conflito com a lei
            20 => true, // Criança ou adolescente com deficiência(s)
            30 => true, // Criança ou adolescente com doença(s) que impeça(m) ou dificulte(m) a frequência à escola
            40 => true, // Criança ou adolescente em abrigo
            50 => true, // Criança ou adolescente em situação de rua
            60 => true, // Criança ou adolescente vítima de abuso / violência sexual
            61 => true, // Crianças ou adolescentes migrantes estrangeiros
            70 => true, // Evasão porque sente a escola desinteressante
            80 => true, // Falta de documentação da criança ou adolescente
            90 => true, // Falta de infraestrutura escolar
            100 => true, // Falta de transporte escolar
            110 => true, // Gravidez na adolescência
            111 => true, // Infrequência escolar reportada pela gestão escolar ou pela rede de ensino
            120 => true, // Preconceito ou discriminação racial
            130 => true, // Trabalho infantil
            140 => true, // Uso, abuso ou dependência de substâncias psicoativas
            150 => true, // Violência familiar
            160 => true, // Violência na escola
            170 => true, // Mudança de domicílio, viagem ou deslocamentos frequentes
            180 => true, // Violência no território
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Processo de atualização das configurações dos grupos iniciado');
        Tenant::where('is_setup', true)->chunk(200, function ($tenants){
            foreach ($tenants as $tenant) {

                $this->comment("-----------------------------------------");
                $this->comment( 'Tenant: '.$tenant->name);
                $primary_group = $tenant->primaryGroup;

                $this->comment('Grupo padrão: '. $primary_group->name);
                $settings_primary_group = $primary_group->getSettings();

                $this->comment('Lida com as causas:');

                foreach ($this->alerts as $key => $alert){
                    $this->comment('Visualiza e notifica: '.$settings_primary_group->handlesAlertCause($key) . ' - Key of cause alert: ' . $key);
                }

                $settings_primary_group->update(['alerts' => $this->alerts]);

                $primary_group->setSettings($settings_primary_group);

                $this->comment("-----------------------------------------");
                $this->comment("");
            }
        });
        $this->comment('Processo de atualização das configurações dos grupos finalizado');
    }
}
