<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvaluationTables extends Migration
{
    /**
     * Exécuter les migrations.
     *
     * @return void
     */
    public function up()
    {
        // Création de la permission
        \Spatie\Permission\Models\Permission::create(['name' => 'evaluation.storeEvaluation']);

        // Table evaluations
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('group_names')->onDelete('restrict'); // Ajout du champ class_id
            $table->foreignId('job_definitions_id')->constrained('job_definitions')->onDelete('cascade')->onUpdate('cascade'); // Ajout du champ job_definitions_id
            $table->text('student_remark')->nullable();
            $table->timestamps();

            // Ajout de la contrainte unique
            $table->unique(['evaluator_id', 'student_id', 'class_id', 'job_definitions_id'], 'evaluations_unique');
        });

        // Table appreciations
        Schema::create('appreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
            $table->date('date');
            $table->tinyInteger('level')->unsigned(); // Ajout de la colonne 'level'
            $table->timestamps();

            // Ajout des index
            $table->index('evaluation_id', 'idx_evaluation_id');
            $table->index('date', 'idx_date');
        });

        // Table criterias
        Schema::create('criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appreciation_id')->constrained('appreciations')->onDelete('cascade');
            $table->string('name');
            $table->tinyInteger('value');
            $table->boolean('checked')->default(false);
            $table->text('remark')->nullable();
            $table->unsignedInteger('position'); // Ajout de la colonne 'position'
            $table->timestamps();

            // Ajout des index
            $table->index('appreciation_id', 'idx_appreciation_id');
            $table->index('checked', 'idx_checked');
        });

        Schema::create('default_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->text('description');
            $table->timestamps();
            $table->unsignedBigInteger('user_id')->nullable(); // Permet les valeurs nulles pour les critères par défaut
            $table->integer('position'); // Ajout de la colonne position
        });
    }

    /**
     * Annuler les migrations.
     *
     * @return void
     */
    public function down()
    {
        // Suppression de la permission en cas de rollback
        \Spatie\Permission\Models\Permission::where('name', 'evaluation.storeEvaluation')->delete();

        // Suppression des tables
        Schema::dropIfExists('criterias');
        Schema::dropIfExists('appreciations');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('default_criterias');
    }
}
