<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up()
    {
        // Vérifier si la permission existe déjà avant de la créer
        if (!Permission::where('name', 'evaluation.storeEvaluation')->exists()) {
            Permission::create(['name' => 'evaluation.storeEvaluation']);
        }

        // Vérification si la table 'evaluations' existe avant de la créer
        if (!Schema::hasTable('evaluations')) {
            Schema::create('evaluations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('class_id')->constrained('group_names')->onDelete('restrict');
                $table->foreignId('job_definitions_id')->constrained('job_definitions')->onDelete('cascade')->onUpdate('cascade');
                $table->text('student_remark')->nullable();
                $table->timestamps();

                $table->unique(['evaluator_id', 'student_id', 'class_id', 'job_definitions_id'], 'evaluations_unique');
            });
        }

        // Vérification si la table 'appreciations' existe avant de la créer
        if (!Schema::hasTable('appreciations')) {
            Schema::create('appreciations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
                $table->date('date');
                $table->tinyInteger('level')->unsigned();
                $table->timestamps();

                $table->index('date');
            });
        }

        // Vérification si la table 'criterias' existe avant de la créer
        if (!Schema::hasTable('criterias')) {
            Schema::create('criterias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('appreciation_id')->constrained('appreciations')->onDelete('cascade');
                $table->string('name');
                $table->tinyInteger('value');
                $table->boolean('checked')->default(false);
                $table->text('remark')->nullable();
                $table->unsignedInteger('position');
                $table->timestamps();

                $table->index('checked');
            });
        }

        // Vérification si la table 'default_criterias' existe avant de la créer
        if (!Schema::hasTable('default_criterias')) {
            Schema::create('default_criterias', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category');
                $table->text('description');
                $table->timestamps();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->integer('position');
            });
        }
    }

    public function down()
    {
        // Supprime les tables si elles existent
        if (Schema::hasTable('default_criterias')) {
            Schema::dropIfExists('default_criterias');
        }

        if (Schema::hasTable('criterias')) {
            Schema::dropIfExists('criterias');
        }

        if (Schema::hasTable('appreciations')) {
            Schema::dropIfExists('appreciations');
        }

        if (Schema::hasTable('evaluations')) {
            Schema::dropIfExists('evaluations');
        }

        // Supprime la permission si elle existe
        Permission::where('name', 'evaluation.storeEvaluation')->delete();
    }
};
