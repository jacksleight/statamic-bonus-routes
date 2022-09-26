<?php

namespace JackSleight\StatamicBonusRoutes\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Antlers;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Support\Arr;
use Statamic\View\View;

class BonusController extends Controller
{
    public function __construct()
    {
        $this->middleware('statamic.web');
    }

    public function collection(Request $request)
    {
        $params = $request->route()->parameters();
        $view = Arr::pull($params, 'view');
        $data = Arr::pull($params, 'data');
        $data = array_merge($params, $data);
        $collection = Collection::find(Arr::pull($params, 'collection'));

        $url = $this->resolveStandardEntryUrl($collection, $params);
        if ($url === false) {
            return (new View)
                ->template($view)
                ->layout(Arr::get($data, 'layout', $collection->layout()))
                ->with($data);
        }

        $entry = Entry::findByUri($url, Site::current()->handle());
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
        $data = array_merge($params, $data);
        $taxonomy = Taxonomy::find(Arr::pull($params, 'taxonomy'));

        $url = $this->resolveStandardTermUrl($taxonomy, $params);
        if ($url === false) {
            return (new View)
                ->template($view)
                ->layout(Arr::get($data, 'layout', 'layout'))
                ->with($data);
        }

        $term = Term::findByUri($url, Site::current()->handle());
        if ($term) {
            return (new View)
                ->template($view)
                ->layout(Arr::get($data, 'layout', 'layout'))
                ->with($data)
                ->cascadeContent($term);
        }

        throw new NotFoundHttpException;
    }

    protected function resolveStandardEntryUrl($collection, $params)
    {
        $params['mount'] = $collection->mount()->url();

        $format = $collection->routes()->get('default');

        preg_match_all('/{\s*([a-zA-Z0-9_\-]+)/', $format, $match);
        $required = $match[1];
        if (! Arr::has($params, $required)) {
            return false;
        }

        $format = preg_replace_callback('/{\s*([a-zA-Z0-9_\-\:\.]+)\s*}/', function ($match) {
            return "{{ {$match[1]} }}";
        }, $format);

        return (string) Antlers::parse($format, $params);
    }

    protected function resolveStandardTermUrl($taxonomy, $params)
    {
        $required = ['slug'];
        if (! Arr::has($params, $required)) {
            return false;
        }

        return "{$taxonomy->handle()}/{$params['slug']}";
    }
}
