<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assessment_criterion_templates')) {
            Schema::create('assessment_criterion_templates', function (Blueprint $table) {
                $table->id();

                $table->string('name');
                $table->text('description')->nullable();

                // NULL allowed (system templates can be global)
                $table->foreignId('user_id')->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                // Category reference (short FK name to avoid MySQL length limits)
                $table->unsignedBigInteger('assessment_criterion_category_id');
                $table->foreign('assessment_criterion_category_id', 'acc_category_fk')
                    ->references('id')
                    ->on('assessment_criterion_categories')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->unsignedTinyInteger('position')->default(1);

                $table->timestamps();

                $table->index(['user_id', 'position']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_criterion_templates');
    }
};
