<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RootLayout extends \Illuminate\View\Component
{
    const VERSION_FILENAME = 'version.txt';

    const VERSION_WIP_FILENAME = 'version-wip.txt';

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $version = Cache::rememberForever('version', function () {

            $tag = shell_exec('git describe --tags');
            $sha = shell_exec('git rev-parse --short HEAD');

            //short sha is appended to $tag if it’s not pointing to tag
            $isRelease = ! Str::contains($tag, $sha);

            $href = 'https://github.com/jonathanMelly/pm2etml-intranet/'.
                ($isRelease ?
                    'releases/tag/'.$tag :
                    'commit/'.$sha
                );

            $versionText = Str::substr($tag, 1);
            $prefixes = [
                'local' => '||DEV|| ',
                'staging' => '/!\\STAGING/!\\ ',
            ];
            if (array_key_exists(app()->environment(), $prefixes)) {
                $versionText = $prefixes[app()->environment()].' '.$versionText;
            }

            return '<a target="_blank" href="'.$href.'">'.$versionText.'</a>';

        });

        return view('layouts.root')->with(compact('version'));
    }
}
