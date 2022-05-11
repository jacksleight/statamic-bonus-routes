<?php

namespace JackSleight\StatamicBonusRoutes\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Antlers;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\View\View;

class FrontendController extends Controller
{
    public function collection(Request $request)
    {
        $params = $request->route()->parameters();
        $view = Arr::pull($params, 'view');
        $data = Arr::pull($params, 'data');
        $collection = Arr::pull($params, 'collection');
        $data = array_merge($params, $data);

        $index = ! Arr::hasAny($params, ['id', 'slug']);

        if ($index) {
            return (new View)
                ->template($view)
                ->layout(Arr::get($data, 'layout', $collection->layout()))
                ->with($data);
        }

        $url = $this->resolveStandardUrl($collection, $params);
        $entry = Data::findByUri($url, Site::current()->handle());

        if ($entry) {
            return (new View)
                ->template($view)
                ->layout(Arr::get($data, 'layout', $entry->layout()))
                ->with($data)
                ->cascadeContent($entry);
        }

        throw new NotFoundHttpException;
    }

    public function taxonomy(Request $request)
    {
        $params = $request->route()->parameters();
        $view = Arr::pull($params, 'view');
        $data = Arr::pull($params, 'data');
        $taxonomy = Arr::pull($params, 'taxonomy');
        $data = array_merge($params, $data);

        $index = ! Arr::hasAny($params, ['id', 'slug']);

        if ($index) {
            return (new View)
                ->template($view)
                ->layout(Arr::get($data, 'layout', 'layout'))
                ->with($data);
        }

        $term = $taxonomy->queryTerms()->where('slug', $params['slug'])->first();
        if ($term) {
            return (new View)
                ->template($view)
                ->layout(Arr::get($data, 'layout', 'layout'))
                ->with($data)
                ->cascadeContent($term);
        }

        throw new NotFoundHttpException;
    }

    protected function resolveStandardUrl($collection, $params)
    {
        $format = $collection->routes()->get('default');

        if (Str::startsWith($format, '{mount}')) {
            $params['mount'] = $collection->mount()->url();
        }

        $format = preg_replace_callback('/{\s*([a-zA-Z0-9_\-\:\.]+)\s*}/', function ($match) {
            return "{{ {$match[1]} }}";
        }, $format);

        return (string) Antlers::parse($format, $params);
    }
}
