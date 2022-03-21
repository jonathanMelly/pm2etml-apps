<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DmzAssetController extends Controller
{
    /**
     * Handle the incoming request.
     * @param string $file
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request,string $file)
    {
        $path = storage_path('dmz-assets/'.$file);

        if (file_exists($path)) {
            $mimeCacheKey = $file.'-mime';

            //cache mimetype
            $mimeType = Cache::rememberForever($mimeCacheKey, function () use($path) {
                return mime_content_type($path);
            });

            return response()->file($path, array('Content-Type' => $mimeType));
        }
        return response(status:404);
    }
}
