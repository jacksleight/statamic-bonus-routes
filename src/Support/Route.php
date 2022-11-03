<?php

namespace JackSleight\StatamicBonusRoutes\Support;

use Statamic\Facades\Entry;

class Route
{
    public static function resolveMountUri($uri, $standard = null)
    {
        return preg_replace_callback('/^\{mount(?::([^}]+))?\}/', function ($match) use ($standard) {
            if (! ($match[1] ?? null)) {
                return $standard;
            }
            $entry = Entry::find($match[1]);
            if (! $entry) {
                return;
            }

            return $entry->url();
        }, $uri);
    }
}
