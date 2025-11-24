<?php

namespace Tests\Feature;

use App\Constants\RoleName;
use App\Models\AcademicPeriod;
use App\Models\Evaluation;
use App\Models\User;
use App\Models\JobDefinition;
use App\Models\Contract;
use App\Models\WorkerContract;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\ContractSeeder;
use Database\Seeders\JobSeeder;
use Database\Seeders\UserV1Seeder;
use Tests\BrowserKitTestCase;
use Illuminate\Support\Facades\Auth;

class EvalPulseTest extends BrowserKitTestCase
{
    protected User $teacher;
    protected User $student;
    protected JobDefinition $job;

    /**
     * @before
     */
    public function setUpLocal()
    {
        $this->afterApplicationCreated(function () {
            $this->multiSeed(
                \Database\Seeders\PermissionV1Seeder::class,
                AcademicPeriodSeeder::class,
                UserV1Seeder::class,
                JobSeeder::class
            );

            $this->teacher = User::role(RoleName::TEACHER)->firstOrFail();
            $this->student = User::role(RoleName::STUDENT)->firstOrFail();
            $this->job = JobDefinition::firstOrFail();
            
            $this->be($this->teacher);
        });
    }

    /**
     * @before
     */
    public function setupDbDataAndStorage()
    {
        $this->afterApplicationCreated(function () {
            // We seed PermissionV1Seeder in setUpLocal to ensure order
            // $this->seed(PermissionV1Seeder::class); 
            \Illuminate\Support\Facades\Storage::fake('local');
            \Illuminate\Support\Facades\Storage::fake('upload');
        });
    }

    public function test_teacher_can_create_evaluation()
    {
        // Ensure we have a clean state
        Evaluation::truncate();

        $this->visit('/dashboard') // Just to ensure we are logged in and session is active
            ->post(route('eval_pulse.store'), [
                'job_definition_id' => $this->job->id,
                'worker' => $this->student->email,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addMonth()->format('Y-m-d'),
                '_token' => csrf_token(),
            ]);

        $this->assertResponseStatus(302); // Redirects to show
        
        $evaluation = Evaluation::first();
        $this->assertNotNull($evaluation);
        $this->assertEquals($this->student->id, $evaluation->eleve_id); // Note: eleve_id is used in store method
        $this->assertEquals($this->teacher->id, $evaluation->teacher_id);
    }

    public function test_teacher_can_update_evaluation_with_lowercase_values()
    {
        // Create an evaluation first
        $evaluation = Evaluation::create([
            'eleve_id' => $this->student->id,
            'teacher_id' => $this->teacher->id,
            'job_definition_id' => $this->job->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'encours',
        ]);

        // Prepare data with lowercase values
        $data = [
            'status' => 'encours',
            'appreciations' => [
                1 => ['value' => 'na', 'remark' => 'Test remark NA'],
                2 => ['value' => 'pa', 'remark' => 'Test remark PA'],
                3 => ['value' => 'a', 'remark' => 'Test remark A'],
                4 => ['value' => 'la', 'remark' => 'Test remark LA'],
            ],
            'general_remark' => 'General remark test',
            '_token' => csrf_token(),
        ];

        $this->visit(route('eval_pulse.show', $evaluation->id))
            ->post(route('eval_pulse.update', $evaluation->id), $data);

        $this->assertResponseStatus(302);

        // Verify data is saved as lowercase
        $latestVersion = $evaluation->versions()->latest()->first();
        $this->assertNotNull($latestVersion);
        
        foreach ($latestVersion->appreciations as $appreciation) {
            $this->assertTrue(in_array($appreciation->value, ['na', 'pa', 'a', 'la']));
            // Specifically check one
            if ($appreciation->criterion_id == 1) {
                $this->assertEquals('na', $appreciation->value);
            }
        }
    }

    public function test_teacher_can_update_evaluation_with_uppercase_values_and_saved_as_lowercase()
    {
        // Create an evaluation first
        $evaluation = Evaluation::create([
            'eleve_id' => $this->student->id,
            'teacher_id' => $this->teacher->id,
            'job_definition_id' => $this->job->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'encours',
        ]);

        // Prepare data with UPPERCASE values
        $data = [
            'status' => 'encours',
            'appreciations' => [
                1 => ['value' => 'NA', 'remark' => 'Test remark NA'],
                2 => ['value' => 'PA', 'remark' => 'Test remark PA'],
                3 => ['value' => 'A', 'remark' => 'Test remark A'],
                4 => ['value' => 'LA', 'remark' => 'Test remark LA'],
            ],
            'general_remark' => 'General remark test',
            '_token' => csrf_token(),
        ];

        $this->visit(route('eval_pulse.show', $evaluation->id))
            ->post(route('eval_pulse.update', $evaluation->id), $data);

        $this->assertResponseStatus(302);

        // Verify data is saved as LOWERCASE despite input being UPPERCASE
        $latestVersion = $evaluation->versions()->latest()->first();
        $this->assertNotNull($latestVersion);
        
        foreach ($latestVersion->appreciations as $appreciation) {
            $this->assertTrue(in_array($appreciation->value, ['na', 'pa', 'a', 'la']));
            // Specifically check one
            if ($appreciation->criterion_id == 1) {
                $this->assertEquals('na', $appreciation->value);
            }
        }
    }
}
