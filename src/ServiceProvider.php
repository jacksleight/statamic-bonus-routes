<?php

namespace JackSleight\StatamicBonusRoutes;

use JackSleight\StatamicBonusRoutes\Listeners\DataChangeSubscriber;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $subscribe = [
        DataChangeSubscriber::class,
    ];

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../config/statamic/bonus_routes.php' => config_path('statamic/bonus_routes.php'),
        ], 'statamic-bonus-routes-config');
    }
}
