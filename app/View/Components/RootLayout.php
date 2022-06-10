<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Cache;

class RootLayout extends \Illuminate\View\Component
{
    const VERSION_FILENAME='version.txt';
    const VERSION_WIP_FILENAME='version-wip.txt';

    /**
     * @inheritDoc
     */
    public function render()
    {
        $version = Cache::rememberForever('version', function () {
            $releasedVersionPath = base_path(self::VERSION_FILENAME);
            $wipVersionPath = base_path(self::VERSION_WIP_FILENAME);

            $versionFile=file_exists($wipVersionPath)?
                $wipVersionPath:$releasedVersionPath;

            return file_exists($versionFile)? trim(file_get_contents($versionFile)):'unknown';
        });

        return view('layouts.root')->with(compact('version'));
    }
}
