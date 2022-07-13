<?php
/**
 * busca-ativa-escolar-api
 * CheckCaseDeadlines.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 16/02/2017, 19:15
 */

namespace BuscaAtivaEscolar\Console\Commands;


use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\CaseSteps\CaseStep;
use BuscaAtivaEscolar\CaseSteps\Pesquisa;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\User;
use Carbon\Carbon;
use Log;

class FixMapLocationChild extends Command
{

    protected $signature = 'maintenance:fix_map_location_child';
    protected $description = 'Set Geo Location in Children';
    private $chunk = 500;

    public function handle()
    {
        Pesquisa::chunk($this->chunk, function ($pesquisas) {
            $today = Carbon::today();
            foreach ($pesquisas as $pesquisa) {
                $childObj = Child::where('id', $pesquisa->child_id)->first();
                if ((($pesquisa->place_address && $pesquisa->place_city_name && $pesquisa->place_uf) && !$childObj->map_geocoded_address) || (($childObj->map_geocoded_address['country'] != NULL) && ($childObj->map_geocoded_address['country'] != 'Brasil'))) {
                    var_dump($childObj->map_geocoded_address['country']);
                    try {
                        $placeAdress = rtrim($pesquisa->place_address, ',');
                        $address = $childObj->updateCoordinatesThroughGeocoding("{$pesquisa->place_city_name},'{$placeAdress}',{$pesquisa->place_uf}");
                        $pesquisa->update([
                            'place_lat' => ($address) ? $address->getLatitude() : null,
                            'place_lng' => ($address) ? $address->getLongitude() : null,
                            'place_map_region' => ($address) ? $address->getSubLocality() : null,
                            'place_map_geocoded_address' => ($address) ? $address->toArray() : null,
                        ]);
                    } catch (\Exception $ex) {
                        Log::debug("[pesquisa.on_update.geocode_addr] ({$childObj->id}) Failed to geocode address: {$ex->getMessage()}");
                    }
                    Log::info('success update location children_id: ' . $childObj->id);
                    $this->comment("putting location in: {$childObj->id}");
                } else {
                    Log::error('fail set location children_id: ' . $childObj->id);
                    $this->comment("Incomplete address or location already used: {$childObj->id}");
                }
            }
            $this->comment("Grupo de {$this->chunk} finalizado");
        });
    }
}