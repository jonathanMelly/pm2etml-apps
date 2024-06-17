<?php

namespace App\Http\Controllers;

use App\Constants\FileFormat;
use App\Exceptions\BadFileFormat;
use App\Http\Requests\StoreAttachmentRequest;
use App\Models\JobDefinitionMainImageAttachment;
use Intervention\Image\Facades\Image;

class JobDefinitionMainImageAttachmentController extends AbstractJobDefinitionAttachmentController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws BadFileFormat
     */
    public function __invoke(StoreAttachmentRequest $request)
    {
        //TODO mime check ?
        $filePathInfo = pathinfo($this->getFileInput($request)->getClientOriginalName());
        if (! in_array($filePathInfo['extension'], FileFormat::IMAGE_FORMATS)) {
            throw new BadFileFormat($filePathInfo['extension'], FileFormat::IMAGE_FORMATS);
        }

        return $this->storeJobDefinitionAnyAttachment($request, true,
            function (JobDefinitionMainImageAttachment $attachment) {
                //Redim image
                $imagePath = uploadDisk()->path($attachment->storage_path);

                Image::make($imagePath)
                    ->fit(FileFormat::JOB_IMAGE_WIDTH, FileFormat::JOB_IMAGE_HEIGHT)
                    ->save($imagePath);
            });
    }
}
