<?php

namespace JackSleight\StatamicBonusRoutes\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Antlers;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Support\Arr;
use Statamic\Support\Str;
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

        $collection = Collection::find($params['collection']);
        $params['collection'] = $collection;

        $url = $this->resolveEntryUrl($collection, $params);

        if ($url === false) {
            return $this->response($params, $params['view'], $params['data'], $collection);
        }
        
        $entry = Entry::findByUri($url, Site::current()->handle());
        $params['entry'] = $entry;

        if ($entry && $entry->published()) {
            return $this->response($params, $params['view'], $params['data'], $collection, $entry);
        }

        throw new NotFoundHttpException;
    }

    public function taxonomy(Request $request)
    {
        $params = $request->route()->parameters();
        
        $taxonomy = Taxonomy::find($params['taxonomy']);
        $params['taxonomy'] = $taxonomy;

        $url = $this->resolveTermUrl($taxonomy, $params);

        if ($url === false) {
            return $this->response($params, $params['view'], $params['data'], $taxonomy);
        }

        $term = Term::findByUri($url, Site::current()->handle());
        $params['term'] = $term;
        if ($term && $term->published()) {
            return $this->response($params, $params['view'], $params['data'], $taxonomy, $term);
        }

        throw new NotFoundHttpException;
    }

    protected function response($params, $view, $data, $type, $content = null)
    {
        $primary = $content ?? $type;

        $template = $primary instanceof CollectionContract
            ? $primary->handle().'.index'
            : $primary->template();
        $layout = $primary->layout();

        $view = app(View::class)
            ->template(Arr::get($data, 'template', $template))
            ->layout(Arr::get($data, 'layout', $layout))
            ->cascadeContent($content)
            ->with($data);
        $params['view'] = $view;

        // if (Str::startsWith($view, 'O:47:"Laravel\\SerializableClosure\\SerializableClosure')) {
        //     return app()->call(unserialize($view)->getClosure(), $params);
        // }
        
        return $view->template($view);
    }

    protected function resolveEntryUrl($collection, $params)
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

    protected function resolveTermUrl($taxonomy, $params)
    {
        $required = ['slug'];
        if (! Arr::has($params, $required)) {
            return false;
        }

        return "{$taxonomy->handle()}/{$params['slug']}";
    }
}
