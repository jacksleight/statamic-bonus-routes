<?php

namespace JackSleight\StatamicBonusRoutes\Mixins;

use Closure;
use JackSleight\StatamicBonusRoutes\Http\Controllers\BonusController;
use JackSleight\StatamicBonusRoutes\Support\Route;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;

class Router
{
    public function bonus()
    {
        return function($type, $uri, $view, $data = []) {
            $mode = Str::before($type, ':');
            $handle = Str::after($type, ':');
            $method = 'bonus'.ucfirst($mode);
    
            return $this->{$method}($handle, $uri, $view, $data);
        };
    }

    public function bonusCollection()
    {
        return function($handle, $uri, $view, $data = [])
        {
            $collection = Collection::findByHandle($handle);
            if (! $collection) {
                return $this;
            }
    
            $uri = Route::resolveMountUri($uri, $collection->mount()->url());

            // if ($view instanceof Closure) {
            //     $view = serialize(new SerializableClosure($view));
            // }
    
            return $this->get($uri, [BonusController::class, 'collection'])
                ->defaults('collection', $handle)
                ->defaults('view', $view)
                ->defaults('data', $data);
        };
    }

    public function bonusTaxonomy()
    {
        return function($handle, $uri, $view, $data = [])
        {
            $taxonomy = Taxonomy::findByHandle($handle);
            if (! $taxonomy) {
                return $this;
            }
    
            $uri = Route::resolveMountUri($uri);

            // if ($view instanceof Closure) {
            //     $view = serialize(new SerializableClosure($view));
            // }
    
            return $this->get($uri, [BonusController::class, 'taxonomy'])
                ->defaults('taxonomy', $handle)
                ->defaults('view', $view)
                ->defaults('data', $data);
        };
    }
}
