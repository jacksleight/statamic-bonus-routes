<?php

namespace JackSleight\StatamicBonusRoutes\Http\Controllers\CP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Statamic\Facades\CP\Toast;
use Statamic\Http\Controllers\CP\CpController;

class RouteCacheController extends CpController
{
    public function index(Request $request)
    {
        $routesCached = app()->routesAreCached();

        return view('statamic-bonus-routes::cp.index', [
            'routesCached' => $routesCached,
        ]);
    }

    public function refresh(Request $request)
    {
        if (app()->routesAreCached()) {
            Artisan::call('route:cache');
        }

        Toast::success('Route cache refreshed');

        return redirect()->back();
    }
}
