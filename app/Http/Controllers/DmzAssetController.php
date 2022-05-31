<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DmzAssetController extends Controller
{
    /**
     * Handle the incoming request.
     * @param string $file
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getFile(Request $request, string $path)
    {
        $disk = uploadDisk();

        if ($disk->exists($path)) {
            $mimeCacheKey = $path . '-mime';

            //cache mimetype
            $mimeType = Cache::rememberForever($mimeCacheKey, function () use ($path,$disk) {
                return $disk->mimeType($path);
            });

            return response()->file($disk->path($path), array('Content-Type' => $mimeType));
        }
        abort(404);
    }


}
