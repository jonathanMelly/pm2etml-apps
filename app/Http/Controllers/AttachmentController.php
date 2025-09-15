<?php

namespace App\Http\Controllers;

use App\Constants\DiskNames;
use App\Constants\MorphTargets;
use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\JobDefinition;
use http\Exception\InvalidArgumentException;
use Illuminate\Http\UploadedFile;

class AttachmentController extends Controller
{
    protected $fileInput = null;

    //Global store
    protected function store(StoreAttachmentRequest $request, Attachment $attachment, ?callable $postProcess = null)
    {
        $file = $this->getFileInput($request);

        //USe filesystem move as a shortcut because for now uploadDisk is just a folder alias...
        if ($storagePath = $file->store(attachmentPathInUploadDisk(temporary: true), DiskNames::UPLOAD)) {
            $attachment->name = basename($file->getClientOriginalName());
            $attachment->storage_path = $storagePath;
            $attachment->size = filesize(uploadDisk()->path($storagePath));

            if ($postProcess != null) {
                $postProcess($attachment);
            }

            $attachment->save();

            return response()->json([
                'id' => $attachment->id,
            ]);

        } else {
            return $this->dataError(__('Cannot save file on server'), 500);
        }

    }

    /**
     * Remove the specified resource from storage.
     * To avoid adding additional routes, for now, any delete is handled here instead in a specific
     * subclass per attachable_type...
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attachment $attachment)
    {
        $user = auth()->user();

        //Permissions check
        if ($attachment->attachable == null) {
            //Check any right linked to attachment creation
            if ($user->cannot('jobDefinitions.create')) {
                $this->deny();
            }
        } else {
            switch ($attachment->attachable_type) {
                case MorphTargets::MORPH2_JOB_DEFINITION:

                    /* @var $job JobDefinition */
                    $job = $attachment->attachable;
                    //JobDef is existing
                    if (! (
                        $user->can('jobDefinitions') ||
                        ($user->canAny(['jobDefinitions.edit', 'jobDefinitions.delete'])
                            && $job->providers->contains('id', '=', $user->id))
                    )) {
                        $this->deny();
                    }

                    break;

                case MorphTargets::MORPH2_WORKER_CONTRACT:
                    $this->authorize('contracts.evaluate');
                    break;

                default:
                    $this->deny('Attachment type not implemented');
                    break;
            }
        }

        return response()
            ->json([
                'id' => $attachment->id,
                'deleted' => $attachment->delete(),
            ]);
    }

    protected function dataError(string $message, int $code = 400)
    {
        return response()->json([
            'error' => $message,
        ], $code);
    }

    protected function deny($message = 'Missing permission')
    {
        abort(403, $message);
    }

    protected function getFileInput(StoreAttachmentRequest $request): UploadedFile
    {
        if ($this->fileInput == null) {
            $file = $request->file('file');

            //DZjs sends as array (file[]) if multiple is set
            //Currently fallback is not handled
            //TODO handle fallback ....
            if (is_array($file)) {
                if (count($file) != 1) {
                    throw new InvalidArgumentException(
                        'Bad file input, only 1 at a time supported', 400);
                }
                $file = array_pop($file);
            }

            $this->fileInput = $file;
        }

        return $this->fileInput;
    }
}
