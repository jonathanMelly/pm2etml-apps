<?php

namespace Tests\Feature;

use App\Constants\RoleName;
use App\Enums\JobPriority;
use App\Enums\RequiredTimeUnit;
use App\Models\JobDefinition;
use App\Models\JobDefinitionDocAttachment;
use App\Models\JobDefinitionMainImageAttachment;
use App\Models\User;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\UserV1Seeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\BrowserKitTestCase;

use function PHPUnit\Framework\assertEquals;

class JobDefinitionCreateUpdateTest extends BrowserKitTestCase
{
    use WithFaker;

    /* @var $teacher User */
    protected User $teacher;

    /**
     * @before
     *
     * @return void
     */
    public function setUpLocal()
    {
        $this->afterApplicationCreated(function () {

            $this->multiSeed(
                AcademicPeriodSeeder::class,
                UserV1Seeder::class,
            );

            $this->teacher = User::role(RoleName::TEACHER)->firstOrFail();
            $this->be($this->teacher);

        });
    }

    /**
     * A basic feature test example.
     */
    public function test_teacher_can_create_a_job(): void
    {
        $providers = User::role(RoleName::TEACHER)
            ->orderBy('id')
            ->take(3)
            ->get(['id'])->pluck('id')->toArray();

        //Mimic Dropzone upload. BrowserKit needs call('post') instead of post to correctly pass file...
        $imageId = $this->call('POST', route('job-definition-main-image-attachment.store'), files: [
            'file' => [UploadedFile::fake()->image('test.png', 499, 147)],
        ])->json('id');

        $attachmentId = $this->call('POST', route('job-definition-doc-attachment.store'), files: [
            'file' => [UploadedFile::fake()->createWithContent('test.zip', 'not a real zip')],
        ])->json('id');

        $output = $this->visit(route('jobDefinitions.create'))
            ->submitForm(trans('Publish job offer'),
                [
                    'title' => 'lol',
                    'description' => 'description',
                    'required_xp_years' => 1,
                    'priority' => 0,
                    'image' => $imageId,
                    'providers' => $providers,
                    'allocated_time' => 25,
                    'one_shot' => 1,
                    'published_date' => today(),
                    'other_attachments' => json_encode(['test.zip' => $attachmentId]),
                    'skills' => json_encode(['group:skill']),
                ]/*, kept as documentation if needed somewhere else
                [
                    'image_data'=>'job.png',
                    'image_data-file' =>
                        [
                        //'name' => 'job.png',
                        'tmp_name' => $imageb64
                        ],
                ]*/
            )
            //
            ->seeText('Emploi "lol" ajouté')
            ->seePageIs('/marketplace')
            ->seeElement('img', ['src' => route('dmz-asset', ['file' => JobDefinitionMainImageAttachment::findOrFail($imageId)->storage_path])])
            ->response->getContent();

        //unlink($image);

        /* @var $createdJob \App\Models\JobDefinition */
        $createdJob = JobDefinition::orderByDesc('id')->first();

        $this->assertEquals('lol', $createdJob->title);
        $this->assertEquals('description', $createdJob->description);
        $this->assertEquals(1, $createdJob->required_xp_years);
        $this->assertEquals(JobPriority::MANDATORY, $createdJob->priority);
        $this->assertEquals(25, $createdJob->allocated_time);
        $this->assertEquals(true, $createdJob->one_shot);
        $this->assertEquals($providers, $createdJob->providers()->get()->pluck('id')->toArray());
        $this->assertEquals($createdJob->id, JobDefinitionDocAttachment::findOrFail($attachmentId)->jobDefinition->id);
        $this->assertEquals($imageId, $createdJob->image->id);

        $this->assertStringContainsString('group: skill', $output);

    }

    //TODO add scenarios with attachments, images , ...
    public function test_teacher_can_update_a_job(): void
    {

        $this->createClientAndJob();

        $response = $this->visit(route('jobDefinitions.edit', ['jobDefinition' => 1]))
            ->submitForm(trans('Save modifications'),
                [
                    'title' => 'update-title',
                    'description' => 'update-desc',
                    'required_xp_years' => 2,
                    'priority' => 2,
                    'image' => 1,
                    'providers' => [1],
                    'allocated_time' => 100,
                    'published_date' => today(),
                    'other_attachments' => json_encode('{}'),
                    'skills' => json_encode(['group2:skill2']),
                ]
            );

        $updatedJob = JobDefinition::firstOrFail();

        $response
            ->seePageIs('/marketplace')
            ->seeText('Emploi "'.$updatedJob->title.'" mis à jour')
            ->seeText('group2: skill2');

        $this->assertEquals('update-title', $updatedJob->title);
        $this->assertEquals('update-desc', $updatedJob->description);
        $this->assertEquals('100', $updatedJob->allocated_time);

        //TODO more checks ( ...)

    }

    public function test_job_edit_form_has_correct_data(): void
    {
        /* @var $job JobDefinition */

        ['client' => $client,'job' => $job] = $this->createClientAndJob();
        $job->update(['allocated_time' => 23]);
        $timeInHour = $job->getAllocatedTime(RequiredTimeUnit::HOUR);
        $timeInPeriod = $job->getAllocatedTime();

        $response = $this->visit(route('jobDefinitions.edit', ['jobDefinition' => $job->id]));

        $response
            ->seePageIs(route('jobDefinitions.edit', ['jobDefinition' => $job->id]))
            ->seeInElement('span', $timeInHour)
            ->seeElement('input', ['value' => $timeInPeriod]);

    }

    public function test_teacher_cannot_create_an_invalid_job(): void
    {

        $this->visit(route('jobDefinitions.create'))
            ->submitForm(trans('Publish job offer'),
                [
                    'title' => 'lol',
                ]
            )
            ->seePageIs('/jobDefinitions/create')
            ->seeText('erreur');

    }

    public function test_teacher_can_delete_job(): void
    {

        //Arrange
        /* @var $job JobDefinition */
        ['client' => $client,'job' => $job] = $this->createClientAndJob();
        self::assertFalse($job->trashed());

        //WHEN
        /* @var $response Response */
        $response = $this->call('POST', "/jobDefinitions/{$job->id}", ['_method' => 'delete']);
        $job->refresh();

        //Then
        assertEquals($response->status(), 302);
        self::assertStringContainsString('/marketplace', $response->content());
        self::assertTrue($job->trashed());

    }

    public function base64url_encode($s)
    {
        return str_replace(['+', '/'], ['-', '_'], base64_encode($s));
    }

    //TODO TEST JOB DELETION !!!
}
