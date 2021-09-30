<?php

namespace BuscaAtivaEscolar\Providers;

use Illuminate\Support\ServiceProvider;
use BuscaAtivaEscolar\LGPD\Interfaces\ILgpd;
use BuscaAtivaEscolar\LGPD\Services\LgpdService;

class LgpdServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind(ILgpd::class, LgpdService::class);
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
