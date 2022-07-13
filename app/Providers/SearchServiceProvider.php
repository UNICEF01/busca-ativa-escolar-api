<?php
/**
 * busca-ativa-escolar-api
 * SearchServiceProvider.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 */

namespace BuscaAtivaEscolar\Providers;

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\City;
use BuscaAtivaEscolar\Observers\SearchableModelObserver;
use BuscaAtivaEscolar\Reports\Reports;
use BuscaAtivaEscolar\School;
use BuscaAtivaEscolar\Search\Search;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider {

    public function boot() {

    	$observer = $this->app->make(SearchableModelObserver::class);

	    Child::observe($observer);
	    City::observe($observer);
	    School::observe($observer);

    }

    public function register() {

    	$esClient = ClientBuilder::create()
		    ->setHosts([env('ELASTICSEARCH_ADDR', '127.0.0.1:9200')])
//		    ->setLogger(ClientBuilder::defaultLogger(storage_path('logs/elastic.log'))) todo ver solução de logs
		    ->build();

	    $this->app->singleton(Search::class, function ($app) use ($esClient) {
		    return new Search($esClient);
	    });

	    $this->app->singleton(Reports::class, function ($app) use ($esClient) {
		    return new Reports($esClient);
	    });

	    $this->app->singleton(SearchableModelObserver::class, function($app) {
		    return new SearchableModelObserver($app->make(Search::class));
	    });
    }
}
