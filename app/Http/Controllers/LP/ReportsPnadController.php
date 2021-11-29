<?php

namespace BuscaAtivaEscolar\Http\Controllers\LP;

use BuscaAtivaEscolar\Http\Controllers\BaseController;

class ReportsPnadController extends BaseController
{
    public function pnad()
    {
        $resqueted = [
            'country' => request(''),
            'capital' => request('capital'),
            'reg' => request('reg'),
            'uf' => request('uf')
        ];
        $typeOfCache = 'country';
        foreach ($resqueted as $key => $value) {
            if (!empty($value)) $typeOfCache = $key;
        }
        $capitais = [
            2800308, 1501402, 3106200, 1400100, 5300108,
            5002704, 5103403, 4106902, 4205407, 2304400,
            5208707, 2507507, 1600303, 2704302, 1302603,
            2408102, 1721000, 4314902, 1100205, 2611606,
            1200401, 3304557, 2927408, 2111300, 3550308,
            2211001, 3205309
        ];
        if (in_array($resqueted[$typeOfCache], $capitais) == false) {
            return response()->json(['status' => 'ok', '_data' => null]);
        }
        $keyOfCache = "pnad_" . ($typeOfCache === 'country' ? $typeOfCache : $typeOfCache . '_' . $resqueted[$typeOfCache]);
        try {

            $storeCaches = \Cache::get($keyOfCache);
            $storeCaches = explode("\n", $storeCaches);
            $dados = [];
            $i = 0;
            foreach ($storeCaches as $storeCache) {
                $dados[$i++] = explode(" ", $storeCache);
            }
            $data = [];
            for ($i = 0; $i < 10; $i++) {
                $data[$dados[$i][0]][$dados[$i][1]] = [
                    "id_localizacao" => $dados[$i][2],
                    "id_faixa_etaria" => $dados[$i][3],
                    "value_masc" => $dados[$i][4],
                    "value_femn" => $dados[$i][5],
                    "value_ba" => $dados[$i][6],
                    "value_pni" => $dados[$i][7],
                    "value_sim" => $dados[$i][8],
                    "value_nao" => $dados[$i][9],
                    "value_pb" => $dados[$i][10],
                    "value_int" => $dados[$i][11],
                    "value_rc" => $dados[$i][12],
                    "total" => $dados[$i][13]
                ];
            }
            return response()->json(['status' => 'ok', '_data' => $data]);
        } catch (\Exception $ex) {
            return $this->api_exception($ex);
        }
    }
}
