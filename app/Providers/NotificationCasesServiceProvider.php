<?php

namespace BuscaAtivaEscolar\Providers;

use BuscaAtivaEscolar\NotificationsCases\Interfaces\INotifications;
use BuscaAtivaEscolar\NotificationsCases\Services\NotificationCasesService;
use Illuminate\Support\ServiceProvider;


class NotificationCasesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(INotifications::class, NotificationCasesService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
