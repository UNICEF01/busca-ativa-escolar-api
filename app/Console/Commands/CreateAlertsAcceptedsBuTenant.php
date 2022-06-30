<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\Tenant;
use Illuminate\Console\Command;

class CreateAlertsAcceptedsBuTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:create_alerts_accepteds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria 500 alertas aceitos para todos os grupos do tenant informado';

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
     * @return int
     */
    public function handle()
    {
        $tenantId = $this->ask("Informe o ID do tenant:");
        $coordinatorId = $this->ask("Informe o ID do coordenador:");
        $tenant = Tenant::where('id', $tenantId)->get()->first();
        $time = date("D M d, Y G:i");
        $groups = Group::where('tenant_id', '=', $tenant->id)->get()->all();

        foreach($groups as $group) {
            for ($i = 1; $i <= 300; $i++) {
                $data = [
                    'name' => 'Caso teste ' . $time,
                    'alert_cause_id' => 170,
                    'group_id' => $group->id,
                    'mother_name' => 'Mãe teste ' . $time,
                    'place_address' => 'Endereço teste ' . $time,
                    'place_city' => $tenant->city_id,
                    'place_neighborhood' => 'Bairro de teste ' . $time,
                ];
                Child::spawnFromAlertData($tenant, $coordinatorId, $data);
            }
        }

        foreach ( Child::where([ ['tenant_id', '=', $tenantId ], ['alert_status', '=', Child::ALERT_STATUS_PENDING ] ])->get() as $child ){
            $child->acceptAlert([]);
        }
    }
}
