<?php

namespace JackSleight\StatamicBonusRoutes;

use Illuminate\Routing\Router;
use JackSleight\StatamicBonusRoutes\Http\Controllers\BonusController;
use JackSleight\StatamicBonusRoutes\Http\Controllers\CP\RouteCacheController;
use JackSleight\StatamicBonusRoutes\Listeners\DataChangeSubscriber;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Utility;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Support\Str;

class ServiceProvider extends AddonServiceProvider
{
    protected $subscribe = [
        // DataChangeSubscriber::class,
    ];

    public function boot()
    {
        $resolveMount = function ($uri, $standard = null) {
            return preg_replace_callback('/^\{mount(?::([^}]+))?\}/', function ($match) use ($standard) {
                if (! ($match[1] ?? null)) {
                    return $standard;
                }
                $entry = Entry::find($match[1]);
                if (! $entry) {
                    return;
                }

                return $entry->url();
            }, $uri);
        };

        Router::macro('bonus', function ($type, $uri, $view, $data = []) {
            $mode = Str::before($type, ':');
            $handle = Str::after($type, ':');
            $method = 'bonus'.ucfirst($mode);

            return $this->{$method}($handle, $uri, $view, $data);
        });

        Router::macro('bonusCollection', function ($handle, $uri, $view, $data = []) use ($resolveMount) {
            $collection = Collection::findByHandle($handle);
            if (! $collection) {
                return $this;
            }
            $uri = $resolveMount($uri, $collection->mount()->url());

            return $this->get($uri, [BonusController::class, 'collection'])
                ->defaults('collection', $handle)
                ->defaults('view', $view)
                ->defaults('data', $data);
        });

        Router::macro('bonusTaxonomy', function ($handle, $uri, $view, $data = []) use ($resolveMount) {
            $taxonomy = Taxonomy::findByHandle($handle);
            if (! $taxonomy) {
                return $this;
            }
            $uri = $resolveMount($uri);

            return $this->get($uri, [BonusController::class, 'taxonomy'])
                ->defaults('taxonomy', $handle)
                ->defaults('view', $view)
                ->defaults('data', $data);
        });

        parent::boot();
    }

    public function bootAddon()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-bonus-routes');

        Utility::make('route_cache')
            ->navTitle('Route Cache')
            ->icon('synchronize')
            ->title('Route Cache Refresh')
            ->description('Refresh the route cache after making changes to your mount entries.')
            ->action([RouteCacheController::class, 'index'])
            ->routes(function ($router) {
                $router->get('/refresh', [RouteCacheController::class, 'refresh'])->name('refresh');
            })
            ->register();
    }
}
