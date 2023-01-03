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
        $resolveMountUri = function ($uri, $standard = null) {
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

        return function($type, $uri, $view = null, $data = []) use ($resolveMountUri) {
            $mode = Str::before($type, ':');
            $handle = Str::after($type, ':');

            if ($mode === 'collection') {
                $collection = Collection::findByHandle($handle);
                if (! $collection) {
                    return $this;
                }
                $uri = $resolveMountUri($uri, $collection->mount()->url());
            } else if ($mode === 'taxonomy') {
                $taxonomy = Taxonomy::findByHandle($handle);
                if (! $taxonomy) {
                    return $this;
                }
                $uri = $resolveMountUri($uri);
            } else {
                return $this;
            }

            return $this->get($uri, [BonusController::class, $mode])
                ->defaults($mode, $handle)
                ->defaults('view', $view)
                ->defaults('data', $data);
        };
    }
}
