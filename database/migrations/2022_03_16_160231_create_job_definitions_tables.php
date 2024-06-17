<?php

use App\Enums\CustomPivotTableNames;
use App\Enums\JobPriority;
use App\Models\JobDefinition;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->table(), function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title');
            $desc = $table->text('description');

            //Does not work with sqlite in memory test db
            if (config('APP_ENV') === 'production') {
                $desc->fulltext();
            }

            $table->unsignedTinyInteger('required_xp_years')->default(0);
            $table->unsignedTinyInteger('priority')->default(JobPriority::RECOMMENDED->value);
            $table->unsignedTinyInteger('max_workers')->default(1);

            $table->timestamp('published_date')->nullable()->default(null);

            $table->unsignedInteger('allocated_time');
            $table->unsignedTinyInteger('allocated_time_unit')->default(\App\Enums\RequiredTimeUnit::PERIOD->value);

            //remember that attachment is STI (single table inheritance) which is attachments
            /*$table
                ->foreignId('image_attachment_id')
                ->constrained(tbl(\App\Models\Attachment::class))
                ->cascadeOnUpdate()->cascadeOnDelete();*/

            $table->boolean('one_shot')->default(false);

            $table->softDeletes();
        });

        //Store providers of a job
        Schema::create(CustomPivotTableNames::USER_JOB_DEFINITION->value, function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(JobDefinition::class)->constrained();
            $table->unique(['user_id', 'job_definition_id']);
        });

        Schema::create('job_definition_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(JobDefinition::class)->constrained();
            $table->foreignIdFor(Skill::class)->constrained();
            $table->unique(['skill_id', 'job_definition_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->table());
        Schema::dropIfExists(CustomPivotTableNames::USER_JOB_DEFINITION->value);
    }

    public function table(): string
    {
        return app(JobDefinition::class)->getTable();
    }
};
