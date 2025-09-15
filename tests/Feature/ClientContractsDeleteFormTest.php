<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\ContractEvaluationAttachment;
use App\Models\JobDefinition;
use App\Models\User;
use App\Models\WorkerContract;
use Illuminate\Http\UploadedFile;
use Tests\BrowserKitTestCase;

class ClientContractsDeleteFormTest extends BrowserKitTestCase
{
    /* @var $teacher User */
    protected User $teacher;

    protected JobDefinition $job;

    protected int $contractsCount = 2;

    private string $formPage;

    /**
     * @before
     *
     * @return void
     */
    public function setUpLocal()
    {
        $this->afterApplicationCreated(function () {
            $clientAndJob = $this->createClientAndJob($this->contractsCount);

            $this->teacher = $clientAndJob['client'];
            $this->job = $clientAndJob['job'];

            $this->formPage = '/dashboard';
        });
    }

    /**
     * A basic feature test example.
     */
    public function test_teacher_can_delete_two_contracts(): void
    {

        $jobId = $this->job->id;
        $contractIds = $this->teacher->contractsAsAClientForJob($this->job, AcademicPeriod::current())->take($this->contractsCount)
            ->get('id')->pluck('id')->toArray();

        $this->assertEquals($this->contractsCount, count($contractIds));

        $contractFields = 'job-'.$jobId.'-contracts';

        $this->visit($this->formPage)
            ->submitForm("job-{$jobId}-form-input-for-test", [
                $contractFields => $contractIds,
                'job_id' => $jobId,
            ])
            ->seePageIs('/dashboard')
            ->seeText($this->contractsCount.' contrats supprimés');

    }

    public function test_teacher_can_delete_contracts_with_pdf_attachments_and_attachments_are_cleaned_up(): void
    {
        $jobId = $this->job->id;
        $contracts = $this->teacher->contractsAsAClientForJob($this->job, AcademicPeriod::current())
            ->take($this->contractsCount)
            ->get();

        $contractIds = $contracts->pluck('id')->toArray();
        $this->assertEquals($this->contractsCount, count($contractIds));

        // Evaluate contracts and add PDF attachments
        $attachmentIds = [];
        foreach ($contracts as $contract) {
            $wc = WorkerContract::query()->where('contract_id', $contract->id)->first();

            // Upload a PDF attachment for the contract (before evaluation)
            $filename = "evaluation-{$contract->id}.pdf";
            $file = UploadedFile::fake()->create($filename, 100, 'application/pdf');

            $response = $this->call('POST', 'contract-evaluation-attachment', [
                'worker_contract_id' => $wc->id,
                '_token' => csrf_token(),
            ], [], [
                'file' => $file
            ]);

            $response->assertStatus(200);
            $responseData = json_decode($response->getContent(), true);
            $attachmentIds[] = $responseData['id'];

            // Verify attachment was created in pending storage
            $attachment = ContractEvaluationAttachment::find($responseData['id']);
            $this->assertNotNull($attachment);
            $this->assertStringContainsString(\App\Constants\FileFormat::ATTACHMENT_TEMPORARY_SUBFOLDER, $attachment->storage_path);
            $this->assertFileExists(uploadDisk()->path($attachment->storage_path));

            // Now evaluate the contract through the controller to finalize attachments (move from pending to permanent)
            $this->visit('/contracts/evaluate/' . $wc->id)
                ->submitForm(trans('Save evaluation results'), [
                    'workersContracts' => [$wc->id],
                    'success-' . $wc->id => 'true',
                ])
                ->seePageIs('/dashboard');

            // Refresh attachment to get updated storage path
            $attachment->refresh();
            $this->assertStringNotContainsString(\App\Constants\FileFormat::ATTACHMENT_TEMPORARY_SUBFOLDER, $attachment->storage_path);
            $this->assertFileExists(uploadDisk()->path($attachment->storage_path));
        }

        // Delete the contracts
        $contractFields = 'job-'.$jobId.'-contracts';
        $this->visit($this->formPage)
            ->submitForm("job-{$jobId}-form-input-for-test", [
                $contractFields => $contractIds,
                'job_id' => $jobId,
            ])
            ->seePageIs('/dashboard')
            ->seeText($this->contractsCount.' contrats supprimés');

        // Verify attachments are soft deleted and encrypted files moved to deleted folder
        foreach ($attachmentIds as $attachmentId) {
            $attachment = ContractEvaluationAttachment::withTrashed()->find($attachmentId);
            $this->assertNotNull($attachment);
            $this->assertNotNull($attachment->deleted_at); // Should be soft deleted
            $this->assertTrue($attachment->shouldBeEncrypted()); // Should be encrypted type
            
            // File should be moved to deleted folder (storage_path is updated by Attachment model)
            $this->assertTrue(uploadDisk()->exists($attachment->storage_path), "Encrypted attachment file should exist at: {$attachment->storage_path}");
            $this->assertStringContainsString(\App\Constants\FileFormat::ATTACHMENT_DELETED_SUBFOLDER, $attachment->storage_path, "File should be in deleted folder");

            // Verify the deleted file is still encrypted (security check)
            $deletedFileContent = uploadDisk()->get($attachment->storage_path);
            $this->assertStringStartsWith('eyJpdiI6', $deletedFileContent); // Should still be encrypted
        }
    }
}
