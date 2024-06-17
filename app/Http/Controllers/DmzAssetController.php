<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DmzAssetController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  string  $file
     * @return \Illuminate\Http\Response
     */
    public function getFile(Request $request, string $path)
    {
        $disk = uploadDisk();

        if ($disk->exists($path)) {
            $mimeCacheKey = $path.'-mime';

            //cache mimetype
            $mimeType = Cache::rememberForever($mimeCacheKey, function () use ($path, $disk) {
                return $disk->mimeType($path);
            });

            return response()->file($disk->path($path), ['Content-Type' => $mimeType]);
        }
        abort(404);
    }
}
