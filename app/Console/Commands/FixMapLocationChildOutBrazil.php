<?php
/**
 * busca-ativa-escolar-api
 *
 *
 * Copyright (c) UNICEF
 *
 * @author Sandy Santos <ssantos@unicef.org>
 *
 * Created at: 12/03/2020
 * Verifica se a referência geográfica está dentro do Brasil e corrige caso não esteja.
 */

namespace BuscaAtivaEscolar\Console\Commands;


use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\CaseSteps\CaseStep;
use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use Log;

class FixMapLocationChildOutBrazil extends Command
{

    protected $signature = 'maintenance:fix_map_location_child_out_brazil';
    protected $description = 'Set Geo Location in Children';
    private $chunk = 500;

    public function handle()
    {
        Pesquisa::chunk($this->chunk, function ($pesquisas) {
            $today = Carbon::today();
            foreach ($pesquisas as $pesquisa) {

                $childObj = Child::where('id', $pesquisa->child_id)->first();

                if (!empty($childObj->lat) && !empty($childObj->lng)) {
                    if (($childObj->lat > 5.3) || ($childObj->lat == 0)) {
                        $pesquisa->update([
                            'place_lat' => null,
                            'place_lng' => null,
                            'place_map_region' => null,
                            'place_map_geocoded_address' => null,
                        ]);
                        $childObj->update([
                            'lat' => null,
                            'lng' => null,
                            'region' => null,
                            'map_geocoded_address' => null,

                        ]);
                        Log::info('success update location children_id as null: ' . $childObj->id);
                        $this->comment("putting location in: {$childObj->id}");
                    }
//                        } catch (\Exception $ex) {
//                            Log::debug("[pesquisa.on_update.geocode_addr] ({$childObj->id}) Failed to geocode address: {$ex->getMessage()}");
//                        }

                } else {
                    Log::error('fail set location children_id: ' . $childObj->id);
                    $this->comment("Incomplete address or location already used: {$childObj->id}");
                }
            }
            $this->comment("Grupo de {$this->chunk} finalizado");
        });
    }
}