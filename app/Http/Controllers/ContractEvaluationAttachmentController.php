<?php

namespace App\Http\Controllers;

use App\Constants\FileFormat;
use App\Http\Requests\StoreAttachmentRequest;
use App\Models\ContractEvaluationAttachment;
use Ramsey\Uuid\Guid\Guid;

class ContractEvaluationAttachmentController extends Controller
{
    public function __invoke(StoreAttachmentRequest $request)
    {
        //TODO also check more with clients/admin rights...
        $this->authorize('contracts.evaluate');

        $filePathInfo = pathinfo($request->file('file')->getClientOriginalName());
        $extension = $filePathInfo['extension'] ?? '';

        if (!in_array(strtolower($extension), FileFormat::CONTRACT_EVALUATION_FORMATS)) {
            return response()->json([
                'error' => __('File format :extension not allowed. Only :formats files are accepted.', [
                    'extension' => $extension,
                    'formats' => implode(', ', FileFormat::CONTRACT_EVALUATION_FORMATS)
                ])
            ], 400);
        }

        return $this->storeContractEvaluationAttachment($request);
    }

    private function storeContractEvaluationAttachment(StoreAttachmentRequest $request)
    {
        /* @var $attachment \App\Models\ContractEvaluationAttachment */
        $attachment = ContractEvaluationAttachment::make();
        $attachment->type = \App\Constants\AttachmentTypes::STI_CONTRACT_EVALUATION_ATTACHMENT;

        if ($request->has('worker_contract_id')) {
            $attachment->attachable_type = \App\Constants\MorphTargets::MORPH2_WORKER_CONTRACT;
            $attachment->attachable_id = $request->input('worker_contract_id');
        }

        $file = $request->file('file');
        //original filename: basename($file->getClientOriginalName()
        // Generate unique filename with contract-doc- prefix
        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        do {
            $uuid = Guid::uuid4()->toString();
            $filename = "eval-" . $uuid . "." .$extension;
            $storagePath = attachmentPathInUploadDisk($filename,temporary: true);
        } while (uploadDisk()->exists($storagePath));

        // Read file content and store with encryption
        $fileContent = $file->get();

        $attachment->name = basename($file->getClientOriginalName());
        $attachment->storage_path = $storagePath;//pending path (to be moved later)
        $attachment->size = strlen($fileContent); // Original file size (before encryption)

        // For ContractEvaluationAttachment, always encrypt
        if ($attachment->storeEncrypted($fileContent, $storagePath)) {
            $attachment->save();

            return response()->json([
                'id' => $attachment->id,
                'name' => $attachment->name,
                'size' => $attachment->size,
            ]);
        } else {
            return response()->json([
                'error' => __('Cannot save file on server'),
            ], 500);
        }
    }
}
