<?php

use Illuminate\Http\UploadedFile;

test('Upload a pdf attachment (mimic dropzone xhr call) without jobid', function () {
    /* @var $this \Tests\TestCase */

    $this->be($this->createUser(roles: \App\Constants\RoleName::TEACHER));

    //Given
    $filename = 'attachment.pdf';

    //When
    //Hard Coded uri as it is in JS part of jobdef-create-update view (detect refactorings)
    /* @var $response \Illuminate\Testing\TestResponse */
    $response = $this->post('job-doc-attachment', [
        'file' => [UploadedFile::fake()->createWithContent($filename,'not a real pdf')],
    ]);

    //Then
    $response->assertStatus(200);
    $response->assertExactJson(['id'=>1]);
    $this->assertFileExists(
        uploadDisk()->path(\App\Models\JobDefinitionDocAttachment::findOrFail(1)->storage_path));

    //And when
    $attachment = \App\Models\JobDefinitionDocAttachment::firstOrFail()
        ->attachJobDefinition(\App\Models\JobDefinition::factory()->create());

    //Then
    $this->assertFileDoesNotExist(uploadDisk()->path(attachmentPathInUploadDisk($attachment->storage_path,true)));
    $this->assertFileExists(uploadDisk()->path(attachmentPathInUploadDisk($attachment->storage_path)));
});

test('Upload an image (mimic dropzone xhr call) without jobid', function () {
    /* @var $this \Tests\TestCase */

    $this->be($this->createUser(roles: \App\Constants\RoleName::TEACHER));

    //Given
    $filename = 'test.png';

    //When
    //Hard Coded uri as it is in JS part of jobdef-create-update view (detect refactorings)
    /* @var $response \Illuminate\Testing\TestResponse */
    $response = $this->post('job-image-attachment', [
        'file' => [UploadedFile::fake()->image('test.png',499,147)],
    ]);

    //Then
    $response->assertStatus(200);
    $response->assertExactJson(['id'=>1]);
    $storedFiles = uploadDisk()->files(attachmentPathInUploadDisk(temporary: true));
    \PHPUnit\Framework\assertCount(1,$storedFiles);

    $storedFile = $storedFiles[0];
    $sizes = getimagesize(uploadDisk()->path($storedFile));
    $this->assertEquals(\App\Constants\FileFormat::JOB_IMAGE_WIDTH,$sizes[0],'Redim failed');
    $this->assertEquals(\App\Constants\FileFormat::JOB_IMAGE_HEIGHT,$sizes[1],'Redim failed');

    //And then
    //Check that image has been successfully moved from pending
    $attachment = \App\Models\JobDefinitionMainImageAttachment::firstOrFail()
        ->attachJobDefinition(\App\Models\JobDefinition::factory()->create());
    $this->assertFileDoesNotExist(uploadDisk()->path(attachmentPathInUploadDisk($attachment->storage_path,true)));
    $this->assertFileExists(uploadDisk()->path(attachmentPathInUploadDisk($attachment->storage_path)));
});

//TODO refactor test using dataset?
test('Delete a linked and an unlinked attachment (mimic axios xhr call)', function () {
    /* @var $this \Tests\TestCase */

    //Given
    $clientAndJob = $this->createClientAndJob(1);
    $job = $clientAndJob['job'];
    $attachment1 = $this->createAttachment('notpending.pdf');
    $attachment1->attachJobDefinition($job);

    $attachment2 = $this->createAttachment('pending.pdf');

    //When
    //Hard Coded uri as it is in JS part of jobdef-create-update view (detect refactorings)
    /* @var $responseForNotPending \Illuminate\Testing\TestResponse */
    $responseForNotPending = $this->delete('attachments/'.$attachment1->id);
    $responseForPending = $this->delete('attachments/'.$attachment2->id);

    //Then
    $responseForNotPending->assertStatus(200);
    $responseForNotPending->assertExactJson(['id'=>$attachment1->id,'deleted'=>true]);
    $this->assertTrue(uploadDisk()->exists(attachmentPathInUploadDisk('notpending.pdf',false,deleted:true)),'Linked DocAttachment file not in deleted folder');
    $this->assertFalse(uploadDisk()->exists(attachmentPathInUploadDisk('notpending.pdf')),'Linked DocAttachment file should not be in standard folder anymore');

    $responseForPending->assertStatus(200);
    $responseForPending->assertExactJson(['id'=>$attachment2->id,'deleted'=>true]);
    $this->assertFalse(uploadDisk()->exists(attachmentPathInUploadDisk('pending.pdf',true)),'Unlinked docattachment should be in deleted only');
    $this->assertTrue(uploadDisk()->exists(attachmentPathInUploadDisk('pending.pdf',true,deleted:true)),'Unlinked docattachment should be in pending deleted');
});

test('Cannot delete an attachment for a job without being it’s provider', function () {
    /* @var $this \Tests\TestCase */

    //Given
    $clientAndJob = $this->createClientAndJob(1);
    $job = $clientAndJob['job'];
    $attachment =$this->createAttachment()->attachJobDefinition($job);
    //Overrides current user
    $otherProvider = $this->createUser(roles:\App\Constants\RoleName::TEACHER);

    //When
    /* @var $response \Illuminate\Testing\TestResponse */
    $response = $this->delete(route('attachment.destroy',['attachment'=>$attachment]));

    //Then
    $response->assertForbidden();
});

test('Employee cannot delete an attachment', function () {
    /* @var $this \Tests\TestCase */

    //Given
    $this->createUser(\App\Constants\RoleName::STUDENT);
    $attachment =$this->createAttachment();

    //When
    /* @var $response \Illuminate\Testing\TestResponse */
    $response = $this->delete(route('attachment.destroy',['attachment'=>$attachment]));

    //Then
    $response->assertForbidden();
});

test('Cannot upload bad file format', function (string $uri,$file) {
    //Given
    $this->be($this->createUser(roles: \App\Constants\RoleName::TEACHER));

    //When
    $response = $this->post($uri, [
        'file' => [$file],
    ]);

    //Then
    $response->assertStatus(415);
})->with([
    [fn()=>route('job-definition-main-image-attachment.store'),
        UploadedFile::fake()->image('test.arv',499,147)],

    [fn()=>route('job-definition-doc-attachment.store'),
        UploadedFile::fake()->createWithContent('test.arv','bad file')],
]);

test('Storage disks cannot be hacked using path traversal ../', function () {

    $this->expectException(\League\Flysystem\PathTraversalDetected::class);
    uploadDisk()->get('test/../../notreachable.txt');

});
