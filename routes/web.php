<?php

use JackSleight\StatamicBonusRoutes\Http\Controllers\FrontendController;
use Statamic\Facades\Collection;
use Statamic\Support\Str;

$collections = config('statamic.bonus_routes.collections');

foreach ($collections as $handle => $routes) {
    foreach ($routes as $name => $route) {
        if (Str::startsWith($route, '{mount}')) {
            $collection = Collection::findByHandle($handle);
            $mount = $collection->mount()->url();
            $route = Str::replaceFirst('{mount}', $mount, $route);
        }
        Route::get($route, [FrontendController::class, 'index'])
            ->defaults('collection', $handle)
            ->name("{$handle}.{$name}");
    }
}
