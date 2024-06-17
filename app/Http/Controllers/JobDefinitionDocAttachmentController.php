<?php

namespace App\Http\Controllers;

use App\Constants\FileFormat;
use App\Exceptions\BadFileFormat;
use App\Http\Requests\StoreAttachmentRequest;

class JobDefinitionDocAttachmentController extends AbstractJobDefinitionAttachmentController
{
    /**
     * @throws BadFileFormat
     */
    public function __invoke(StoreAttachmentRequest $request)
    {
        //Revalidate extension TODO mime check ?
        $filePathInfo = pathinfo($this->getFileInput($request)->getClientOriginalName());
        if (! in_array($filePathInfo['extension'], FileFormat::JOB_DOC_ATTACHMENT_ALLOWED_EXTENSIONS)) {
            throw new BadFileFormat($filePathInfo['extension'], FileFormat::JOB_DOC_ATTACHMENT_ALLOWED_EXTENSIONS);
        }

        return $this->storeJobDefinitionAnyAttachment($request);
    }
}
