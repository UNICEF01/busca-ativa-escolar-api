<?php

namespace BuscaAtivaEscolar\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

    protected $listen = [
	    'BuscaAtivaEscolar\Events\SearchableNeedsReindexing' => [
		    'BuscaAtivaEscolar\Listeners\SearchIndexer',
	    ],
    ];

    protected $subscribe = [
    	\BuscaAtivaEscolar\Listeners\ChildActivityNotificationGenerator::class,
    	\BuscaAtivaEscolar\Listeners\ChildActivityLogger::class,
    ];

    public function boot() {
        parent::boot();
    }
}
