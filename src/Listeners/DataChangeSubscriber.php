<?php

namespace JackSleight\StatamicBonusRoutes\Listeners;

use Illuminate\Support\Facades\Artisan;
use Statamic\Events\CollectionTreeSaved;
use Statamic\Events\EntrySaved;

class DataChangeSubscriber
{
    public function subscribe($events)
    {
        return [
            EntrySaved::class => 'handleEntry',
            CollectionTreeSaved::class => 'handleCollectionTree',
        ];
    }

    public function handleEntry($event)
    {
        if ($event->entry->collection()->handle() === 'pages') {
            $this->refresh($event);
        }
    }

    public function handleCollectionTree($event)
    {
        if ($event->tree->collection()->handle() === 'pages') {
            $this->refresh($event);
        }
    }

    protected function refresh($event)
    {
        if (app()->routesAreCached()) {
            Artisan::call('route:cache');
        }
    }
}
