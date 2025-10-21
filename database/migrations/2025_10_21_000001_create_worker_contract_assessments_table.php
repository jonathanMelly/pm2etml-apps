<?php

use App\Models\JobDefinition;
use App\Models\User;
use App\Models\WorkerContract;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('worker_contract_assessments')) {
            Schema::create('worker_contract_assessments', function (Blueprint $table) {
                $table->id();

                // Liens principaux
                $table->foreignIdFor(WorkerContract::class, 'worker_contract_id')
                    ->constrained(WorkerContract::query()->getModel()->getTable())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignIdFor(User::class, 'teacher_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignIdFor(User::class, 'student_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                // Métadonnées contextuelles
                $table->foreignIdFor(JobDefinition::class, 'job_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                // Classe (référence à group_names)
                $table->foreignId('class_id')
                    ->nullable()
                    ->constrained('group_names')
                    ->nullOnDelete();

                $table->string('job_title')->default('');
                $table->string('status')->default('not_started');

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_contract_assessments');
    }
};

