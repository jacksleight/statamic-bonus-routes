<?php

namespace JackSleight\StatamicBonusRoutes\Listeners;

use Illuminate\Support\Facades\Artisan;
use Statamic\Events\EntrySaved;

class DataChangeSubscriber
{
    public function handle($event)
    {
        if (app()->routesAreCached()) {
            Artisan::call('route:cache');
        }
    }

    public function subscribe($events)
    {
        return [
            EntrySaved::class => 'handle',
        ];
    }
}
