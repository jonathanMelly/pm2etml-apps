<?php

use App\Models\WorkerContractAssessment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assessments')) {
            Schema::create('assessments', function (Blueprint $table) {
                $table->id();

                $table->foreignIdFor(WorkerContractAssessment::class, 'worker_contract_assessment_id')
                    ->constrained('worker_contract_assessments')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->timestamp('date')->nullable();
                $table->string('timing');
                $table->text('student_remark')->nullable();

                $table->timestamps();

                $table->unique(['worker_contract_assessment_id', 'timing'], 'assessments_unique_eval_per_timing');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};

