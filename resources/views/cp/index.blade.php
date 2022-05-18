@extends('statamic::layout')
@section('title', __('Backup'))
 
@section('content')
    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities')
        ])
        <h1>{{ __('Route Cache Refresh') }}</h1>
    </header>

    <div class="card">
        @if ($routesCached)
            <div class="flex items-center justify-between">
                <div>Routes are currently cached, click to refresh:</div>
                <form action="{{ cp_route('utilities.route_cache.refresh') }}" method="post">
                    <button class="btn-primary">{{ __('Refresh Route Cache') }}</button>
                    @csrf
                </form>
            </div>
        @else
            <div class="flex items-center">
                <div>Routes are not currently cached, no refresh is necessary.</div>
            </div>
        @endif
    </div>
@stop