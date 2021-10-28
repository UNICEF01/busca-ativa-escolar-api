<?php

namespace BuscaAtivaEscolar\Providers;

use Illuminate\Support\ServiceProvider;
use BuscaAtivaEscolar\LGPD\Interfaces\ILgpd;
use BuscaAtivaEscolar\LGPD\Services\LgpdService;
use BuscaAtivaEscolar\LGPD\Interfaces\IMail;
use BuscaAtivaEscolar\LGPD\Services\LgpdMailService;

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
        $this->app->bind(IMail::class, LgpdMailService::class);
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
