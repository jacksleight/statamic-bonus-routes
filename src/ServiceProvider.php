<?php

namespace JackSleight\StatamicBonusRoutes;

use Illuminate\Routing\Router;
use JackSleight\StatamicBonusRoutes\Http\Controllers\CP\RouteCacheController;
use JackSleight\StatamicBonusRoutes\Mixins\Router as RouterMixin;
use Statamic\Facades\Utility;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        Router::mixin(new RouterMixin);

        parent::boot();
    }

    public function bootAddon()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-bonus-routes');

        Utility::extend(function () {
            Utility::register('route_cache')
                ->navTitle('Route Cache')
                ->icon('synchronize')
                ->title('Route Cache Refresh')
                ->description('Refresh the route cache after making changes to your mount entries.')
                ->action([RouteCacheController::class, 'index'])
                ->routes(function ($router) {
                    $router->post('/refresh', [RouteCacheController::class, 'refresh'])->name('refresh');
                    $router->get('/refresh-success', [RouteCacheController::class, 'refreshSuccess'])->name('refresh_success');
                });
        });
    }
}
