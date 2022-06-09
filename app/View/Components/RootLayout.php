<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Cache;

class RootLayout extends \Illuminate\View\Component
{
    const VERSION_FILENAME='version.txt';

    /**
     * @inheritDoc
     */
    public function render()
    {

        $version = Cache::rememberForever('version', function () {
            return file_exists(base_path(self::VERSION_FILENAME)) ?
                trim(file_get_contents(base_path(self::VERSION_FILENAME))) : 'unknown';
        });

        return view('layouts.root')->with(compact('version'));
    }
}
