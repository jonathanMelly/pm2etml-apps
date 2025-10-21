<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assessment_criteria')) {
            Schema::create('assessment_criteria', function (Blueprint $table) {
                $table->id();

                $table->foreignId('assessment_id')
                    ->constrained('assessments')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                // Template du critère (référencé si la table des templates existe)
                $table->unsignedBigInteger('template_id');

                $table->string('timing');
                $table->unsignedTinyInteger('value')->default(0);
                $table->boolean('checked')->default(false);
                $table->text('remark_criteria')->nullable();
                $table->unsignedTinyInteger('position')->default(1);

                // Pas de timestamps (le modèle les désactive)

                $table->index('assessment_id');
                $table->index('template_id');
                $table->unique(['assessment_id', 'position'], 'assessment_criteria_unique_position');
            });

            // Ajout conditionnel de la contrainte FK si la table existe
            if (Schema::hasTable('assessment_criterion_templates')) {
                Schema::table('assessment_criteria', function (Blueprint $table) {
                    $table->foreign('template_id')
                        ->references('id')
                        ->on('assessment_criterion_templates')
                        ->cascadeOnUpdate();
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_criteria');
    }
};

