<?php


namespace BuscaAtivaEscolar\Traits;


use Illuminate\Support\Facades\Log;

trait LocationHereTrait
{

    public function getLocationByRawAddress($rawAddress){

        try{

            $endPoint = env('HERE_API_ENDPOINT');
            $key = env('HERE_API_KEY');
            $client = new \GuzzleHttp\Client();
            $request = $client->request('GET', $endPoint . '?searchtext=' . $rawAddress . '&gen=9&apiKey=' . $key);
            $stream = json_decode($request->getBody()->getContents());

            if( property_exists($stream, 'Response') ){
                if( count($stream->Response->View) >= 1){
                    $location = $stream->Response->View[0]->Result[0]->Location;
                }else{
                    $location = null;
                }
            }else{
                $location = null;
            }

            return $location;

        }catch (\Exception $exception){
            Log::info($exception->getMessage());
            return null;
        }

    }

}