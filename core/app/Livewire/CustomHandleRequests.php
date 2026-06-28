<?php

namespace App\Livewire;

use Livewire\Mechanisms\HandleRequests\HandleRequests;

class CustomHandleRequests extends HandleRequests
{
    function getUpdateUri()
    {
        if ($this->updateRoute) {
            $uri = route($this->updateRoute->getName(), [], false);
        } else {
            $uri = '/livewire/update';
        }

        $uri = (string) str($uri)->start('/');

        $appUrl = parse_url(config('app.url'), PHP_URL_PATH);
        $appUrl = rtrim($appUrl, '/');

        if ($appUrl && !str_starts_with($uri, $appUrl)) {
            $uri = $appUrl . $uri;
        }

        return $uri;
    }
}
