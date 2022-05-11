<?php

namespace JackSleight\StatamicBonusRoutes;

use Illuminate\Routing\Router;
use JackSleight\StatamicBonusRoutes\Http\Controllers\FrontendController;
use JackSleight\StatamicBonusRoutes\Listeners\DataChangeSubscriber;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Support\Str;

class ServiceProvider extends AddonServiceProvider
{
    protected $subscribe = [
        DataChangeSubscriber::class,
    ];

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__.'/../config/statamic/bonus_routes.php', 'statamic.bonus_routes',
        );
    }

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

        Router::macro('bonus', function ($handle, $uri, $view, $data = []) {
            return $this->bonusCollection($handle, $uri, $view, $data);
        });

        Router::macro('bonusCollection', function ($handle, $uri, $view, $data = []) use ($resolveMount) {
            $collection = Collection::findByHandle($handle);
            if (! $collection) {
                return $this;
            }
            $uri = $resolveMount($uri, $collection->mount()->url());

            return $this->get($uri, [FrontendController::class, 'collection'])
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

            return $this->get($uri, [FrontendController::class, 'taxonomy'])
                ->defaults('taxonomy', $handle)
                ->defaults('view', $view)
                ->defaults('data', $data);
        });

        parent::boot();

        $this->publishes([
            __DIR__.'/../config/statamic/bonus_routes.php' => config_path('statamic/bonus_routes.php'),
        ], 'statamic-bonus-routes-config');
    }
}
