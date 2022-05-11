<?php

namespace JackSleight\StatamicBonusRoutes\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Support\Arr;
use Statamic\View\View;

class FrontendController extends Controller
{
    public function index(Request $request)
    {
        $name = $request->route()->getName();
        $params = $request->route()->parameters();
        $collection = Arr::pull($params, 'collection');

        $show = false;
        if ($id = $request->route('id')) {
            $show = ['id', $id];
        } elseif ($slug = $request->route('slug')) {
            $show = ['slug', $slug];
        }

        if ($show) {
            $data = $collection->queryEntries()
                ->where($show[0], $show[1])
                ->first();
            if (! $data) {
                throw new NotFoundHttpException;
            }

            return (new View)
                ->template($name)
                ->layout($data->layout())
                ->with($params)
                ->cascadeContent($data);
        }

        return (new View)
            ->template($name)
            ->layout($collection->layout())
            ->with($params);
    }
}
