<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\JobDefinitionDocAttachment;
use App\Models\JobDefinitionMainImageAttachment;

abstract class AbstractJobDefinitionAttachmentController extends AttachmentController
{
    protected function storeJobDefinitionAnyAttachment(StoreAttachmentRequest $request,
        bool $image = false,
        ?callable $postProcess = null)
    {

        if ($request->user()->cannot(['jobDefinitions.create'])) {
            abort(403, 'Missing permission');
        }

        $attachment = $image ? JobDefinitionMainImageAttachment::make() : JobDefinitionDocAttachment::make();

        //TODO is this still possible ? -> for an edit/update perhaps ?
        if ($request->has('job_definition_id')) {
            $attachment->attachable->associate($request->input('job_definition_id'));
        }

        return $this->store($request, $attachment, $postProcess);
    }
}
