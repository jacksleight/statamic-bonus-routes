<?php

namespace JackSleight\StatamicBonusRoutes\Listeners;

use Illuminate\Support\Facades\Artisan;
use Statamic\Events\CollectionSaved;
use Statamic\Events\EntrySaved;
use Statamic\Events\TaxonomySaved;
use Statamic\Events\TreeSaved;

class DataChangeSubscriber
{
    public function subscribe($events)
    {
        return [
            TaxonomySaved::class => 'handle',
            CollectionSaved::class => 'handle',
            EntrySaved::class => 'handleEntry',
            TreeSaved::class => 'handleTree',
        ];
    }

    public function handleEntry($event)
    {
        $refresh = config('statamic.bonus_routes.refresh_cache');
        $handle = $event->entry->collection()->handle();

        if (in_array($handle, $refresh)) {
            $this->handle($event);
        }
    }

    public function handleTree($event)
    {
    }

    public function handle($event)
    {
        if (app()->routesAreCached()) {
            Artisan::call('route:cache');
        }
    }
}
