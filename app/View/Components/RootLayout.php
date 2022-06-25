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

            //For release, wip file contains release version
            $wipShaOrReleaseVersion=file_exists($wipVersionPath)?trim(file_get_contents($wipVersionPath)):null;
            $releaseVersion=file_exists($releasedVersionPath)?trim(file_get_contents($releasedVersionPath)):null;

            if($releaseVersion!=null)
            {
                $releaseVersionWithLink = '<a target="_blank" href="https://github.com/jonathanMelly/pm2etml-intranet/releases/tag/v'.$releaseVersion.'">'.$releaseVersion.'</a>';
            }
            else
            {
                $releaseVersionWithLink = null;
            }

            if(app()->environment('local'))
            {
                return '/!\DEV/!\ (~'.($releaseVersionWithLink??'unknown').')';
            }
            //Release
            else if(empty($wipShaOrReleaseVersion) /*prod*/ || $wipShaOrReleaseVersion === $releaseVersion /*staging*/)
            {
                return $releaseVersionWithLink;
            }
            else
            {
                return '[!]STAGING[!] ('.($releaseVersionWithLink??'unknown')
                    .'#<a target="_blank" href="https://github.com/jonathanMelly/pm2etml-intranet/commit/'.$wipShaOrReleaseVersion.'">'.$wipShaOrReleaseVersion.'</a>)';
            }

        });

        return view('layouts.root')->with(compact('version'));
    }
}
