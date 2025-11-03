<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

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
            $attachment = Attachment::where('storage_path', $path)->first();

            if ($attachment) {
                // Use getFileContent for all attachments (handles encryption automatically)
                $fileContent = $attachment->getFileContent();
                $mimeCacheKey = $path.'-mime';
                $mimeType = Cache::rememberForever($mimeCacheKey, function () use ($path, $disk) {
                    return $disk->mimeType($path);
                });

                if ($request->has("name")) {
                    return response($fileContent, 200, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'attachment; filename="' . decrypt($request->get("name")) . '"'
                    ]);
                }

                return response($fileContent, 200, ['Content-Type' => $mimeType]);
            }

            // Fallback for files not tracked in attachments table
            $filePath = $disk->path($path);
            if (!file_exists($filePath)) {
                abort(404);
            }

            $mimeCacheKey = $path.'-mime';
            $mimeType = Cache::rememberForever($mimeCacheKey, function () use ($path, $disk) {
                return $disk->mimeType($path);
            });

            if ($request->has("name")) {
                return response()->download($filePath,
                    Crypt::decryptString($request->get("name")), ['Content-Type' => $mimeType]);
            }

            return response()->file($filePath, ['Content-Type' => $mimeType]);
        }
        abort(404);
    }
}
