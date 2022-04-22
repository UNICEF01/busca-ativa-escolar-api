<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\Http\Controllers\Resources\CitiesController;
use BuscaAtivaEscolar\School;
use BuscaAtivaEscolar\Tenant;
use BuscaAtivaEscolar\TenantSignup;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateGroupsAndUsersTest extends Command
{
    protected $signature = 'test:criando_cidade_estado';
    protected $description = 'Cria uma cidade-estado com perfis defaults';

    function cleanText($string) {
        return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
    }

    public function handle(){

        $nameCityState = $this->ask("Informe o nome da cidade-estado: ");
        $nameUFCityState = $this->ask("Informe a sigla do estado: ");
        $nameRegionCityState = $this->ask("Informe a sigla da região com duas letras: ");
        $numberOfStateIBGE = $this->ask("Informe o número do estado no IBGE: ");

        $registeredCity = City::where([['name', '=', $nameCityState],['uf', '=', $nameUFCityState]])->get()->first();

        if($registeredCity == null){

            $cityState = new City();
            $cityState->region = $nameRegionCityState;
            $cityState->uf = $nameUFCityState;
            $cityState->slug = null;
            $cityState->name= strtoupper($nameCityState);
            $cityState->name_ascii = strtoupper($this->cleanText($nameCityState));
            $cityState->ibge_city_id = null;
            $cityState->ibge_uf_id = $numberOfStateIBGE;
            $cityState->ibge_region_id = null;
            $cityState->webdoc_url = null;
            $cityState->created_at = Carbon::now();
            $cityState->updated_at = Carbon::now();
            $cityState->deleted_at = null;

            $cityState->save();
            \Log::info("Cidade registrada com sucesso - Seguindo para registro do Tenant Signup:");

            $cpfGestorPolitico = $this->ask("Informe o CPF do gestor político com pontos XXX.XXX.XXX-XX: ");
            $birthDayGestorPolitico = $this->ask("Informe a data de nascimento gestor político 1977-07-20: ");
            $nameGestorPolitico =  $this->ask("Informe o nome do gestor político: ");
            $emailGestorPolitico =  $this->ask("Informe o email do gestor político: ");
            $cellPhoneGestorPolitico =  $this->ask("Informe o número do celular do Gestor político: ");
            $positionGestorPolitico =  $this->ask("Informe o cargo do gestor político: ");
            $institutionGestorPolitico = $this->ask("Informe o nome do órgão onde trabalha: ");
            $passwordGestorPolitico = $this->ask("Informe uma senha de acesso: ");

            $cpfPrefeito = $this->ask("Informe o CPF do prefeito com pontos XXX.XXX.XXX-XX");
            $birthDayPrefeito = $this->ask("Informe a data de nascimento do prefeito 1977-07-20: ");
            $namePrefeito =  $this->ask("Informe o nome do prefeito: ");
            $emailPrefeito =  $this->ask("Informe o email do prefeito: ");
            $cellPhonePrefeito =  $this->ask("Informe o número do celular do prefeito: ");
            $tituloprefeito =  $this->ask("Informe o numero do titulo do prefeito: ");

            $city = City::where([['name', '=', $nameCityState],['uf', '=', $nameUFCityState]])->get()->first();

            $dataSignature = array (
                'admin' =>
                    array (
                        'cpf' => $cpfGestorPolitico,
                        'dob' => $birthDayGestorPolitico,
                        'name' => $nameGestorPolitico,
                        'email' => $emailGestorPolitico,
                        'phone' => $cellPhoneGestorPolitico,
                        'mobile' => $cellPhoneGestorPolitico,
                        'position' => $positionGestorPolitico,
                        'institution' => $institutionGestorPolitico,
                        'password' => password_hash($passwordGestorPolitico, 1),
                        'type' => User::TYPE_GESTOR_POLITICO,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'tenant_id' => null,
                        'city_id' => $city->id,
                        'uf' => $nameUFCityState,
                        'group_id' => null,
                        'work_mobile' => '',
                        'personal_mobile' => '',
                        'skype_username' => '',
                        'work_city_id' => $city->id,
                        'lgpd' => true
                    ),
                'mayor' =>
                    array (
                        'cpf' => $cpfPrefeito,
                        'dob' => $birthDayPrefeito,
                        'name' => $namePrefeito,
                        'email' => $emailPrefeito,
                        'phone' => $cellPhonePrefeito,
                        'titulo' => $tituloprefeito
                    ),
                'city_id' => $city->id
            );

            $tenantSignUp = new TenantSignup();
            $tenantSignUp->city_id = $city->id;
            $tenantSignUp->tenant_id = null;
            $tenantSignUp->is_approved = true;
            $tenantSignUp->is_provisioned = true;
            $tenantSignUp->is_approved_by_mayor = true;
            $tenantSignUp->ip_addr = null;
            $tenantSignUp->user_agent = null;
            $tenantSignUp->data  = json_encode($dataSignature);
            $tenantSignUp->created_at = Carbon::now();
            $tenantSignUp->updated_at = Carbon::now();
            $tenantSignUp->deleted_at = null;
            $tenantSignUp->judged_by = null;
            $tenantSignUp->is_state = true;

            $tenantSignUp->save();

            \Log::info("Tenant Signup registrado com sucesso - Seguindo para registro do Tenant:");

            $nameCoordenador = $this->ask("Informe o nome do coordenador");
            $emailCoordenador = $this->ask("Informe o email do coordenador");
            $cellPhoneCoordenador = $this->ask("Informe o celular do coordenador");
            $cpfCoordenador = $this->ask("Informe o CPF do coordenador XXX.XXX.XXX-XX");
            $birthDayCoordenador = $this->ask("Informe a data de nascimento do coordenador 1977-07-20");
            $passwordCoordenador = $this->ask("Informe uma senha de acesso: ");

            $primaryGroup = new Group();
            $primaryGroup->tenant_id = null;
            $primaryGroup->uf = null;
            $primaryGroup->name = "Secretaria de Estado de Educação";
            $primaryGroup->is_primary = true;
            $primaryGroup->created_at = Carbon::now();
            $primaryGroup->updated_at = Carbon::now();
            $primaryGroup->deleted_at = null;
            $primaryGroup->settings = null;
            $primaryGroup->parent_id = null;
            $primaryGroup->save();

            $user = new User();
            $user->name = $nameCoordenador;
            $user->email = $emailCoordenador;
            $user->password = password_hash($passwordCoordenador,1);
            $user->remember_token = null;
            $user->created_at = Carbon::now();
            $user->updated_at = Carbon::now();
            $user->type = User::TYPE_GESTOR_OPERACIONAL;
            $user->tenant_id = null;
            $user->city_id = $city->id;
            $user->uf = $nameUFCityState;
            $user->group_id = $primaryGroup->id;
            $user->deleted_at = null;
            $user->dob  = $birthDayCoordenador;
            $user->cpf = $cpfCoordenador;
            $user->work_phone = $cellPhoneCoordenador;
            $user->work_mobile = $cellPhoneCoordenador;
            $user->personal_mobile = $cellPhoneCoordenador;
            $user->skype_username = null;
            $user->work_address = null;
            $user->work_cep = null;
            $user->work_neighborhood = null;
            $user->work_uf = $nameUFCityState;
            $user->institution = "Secretaria de Estado de Educação";
            $user->position = "Coordenador Operacional";
            $user->work_city_id = $city->id;
            $user->work_city_name = $cityState->name;
            $user->settings = null;
            $user->lgpd = true;

            $user->save();

            $user2 = User::create($dataSignature['admin']);

            $signup = TenantSignup::where('city_id', '=', $city->id)->get()->first();

            $tenant = new Tenant();
            $tenant->city_id = $city->id;
            $tenant->uf = $nameUFCityState;
            $tenant->signup_id = $signup->id;
            $tenant->primary_group_id = $primaryGroup->id;

            $tenant->operational_admin_id = $user->id;
            $tenant->political_admin_id = $user2->id;

            $tenant->is_registered = true;
            $tenant->is_active = true;
            $tenant->is_setup = true;
            $tenant->last_active_at = Carbon::now();
            $tenant->registered_at = Carbon::now();
            $tenant->activated_at = Carbon::now();
            $tenant->created_at = Carbon::now();
            $tenant->updated_at = Carbon::now();
            $tenant->deleted_at = null;
            $tenant->name = $nameCityState;
            $tenant->name_ascii = ord($nameCityState);;
            $tenant->settings = null;
            $tenant->map_lat = null;
            $tenant->map_lng = null;
            $tenant->educacenso_import_details = null;
            $tenant->is_state = true;

            $tenant->save();

            $primaryGroup->tenant_id = $tenant->id;
            $user->tenant_id = $tenant->id;
            $user2->tenant_id = $tenant->id;

            $primaryGroup->save();
            $user->group_id = $primaryGroup->id;
            $user2->group_id = $primaryGroup->id;
            $user->save();
            $user2->save();

            //criar estrutura de grupos:
            $numberOfCities = City::where('uf', $nameUFCityState)->get()->count();

            $numberRegional = 1;

            City::where('uf', $nameUFCityState)->chunk($numberOfCities, function ($cities) use ($numberRegional, $tenant, $primaryGroup){

                $regionalGroup = new Group();
                $regionalGroup->tenant_id = $tenant->id;
                $regionalGroup->uf = null;
                $regionalGroup->name = "Regional ".strval($numberRegional);
                $regionalGroup->is_primary = false;
                $regionalGroup->created_at = Carbon::now();
                $regionalGroup->updated_at = Carbon::now();
                $regionalGroup->deleted_at = null;
                $regionalGroup->settings = null;
                $regionalGroup->parent_id = $primaryGroup->id;
                $regionalGroup->save();

                foreach ($cities as $city){

                    $cityGroup = new Group();
                    $cityGroup->tenant_id = $tenant->id;
                    $cityGroup->uf = null;
                    $cityGroup->name = $city->name;
                    $cityGroup->is_primary = false;
                    $cityGroup->created_at = Carbon::now();
                    $cityGroup->updated_at = Carbon::now();
                    $cityGroup->deleted_at = null;
                    $cityGroup->settings = null;
                    $cityGroup->parent_id = $regionalGroup->id;
                    $cityGroup->save();

                    $schools = School::where('city_id', $city->id)->get()->all();

                    foreach ($schools as $school){

                        $schoolGroup = new Group();
                        $schoolGroup->tenant_id = $tenant->id;
                        $schoolGroup->uf = null;
                        $schoolGroup->name = $school->name;
                        $schoolGroup->is_primary = false;
                        $schoolGroup->created_at = Carbon::now();
                        $schoolGroup->updated_at = Carbon::now();
                        $schoolGroup->deleted_at = null;
                        $schoolGroup->settings = null;
                        $schoolGroup->parent_id = $cityGroup->id;
                        $schoolGroup->save();

                    }

                }

            });

        }
        \Log::info("Cidade não pode ser registrada. Já existe um cadastro com o nome e estado informado.");

    }

}
