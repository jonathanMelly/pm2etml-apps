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

        // Table users
        Schema::table('users', function (Blueprint $table) {
            $table->index('email', 'idx_email');
        });

        // Table role_has_permissions
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->index('permission_id', 'idx_permission_id');
        });

        // Table evaluations
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('group_names')->onDelete('restrict'); // Ajout du champ class_id
            $table->foreignId('job_definitions_id')->constrained('job_definitions')->onDelete('cascade')->onUpdate('cascade'); // Ajout du champ job_definitions_id
            $table->text('student_remark')->nullable();
            $table->timestamps();

            // Ajout des index
            $table->index('evaluator_id', 'idx_evaluator_id');
            $table->index('student_id', 'idx_student_id');
            $table->index('job_definitions_id', 'idx_project_name'); // Index pour job_definitions_id
            $table->index('class_id', 'evaluations_group_names_FK');
            
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

        // Suppression des index
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_email');
        });

        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropIndex('idx_permission_id');
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropIndex('idx_evaluator_id');
            $table->dropIndex('idx_student_id');
            $table->dropIndex('idx_project_name');
            $table->dropIndex('evaluations_group_names_FK');
        });

        Schema::table('appreciations', function (Blueprint $table) {
            $table->dropIndex('idx_evaluation_id');
            $table->dropIndex('idx_date');
        });

        Schema::table('criterias', function (Blueprint $table) {
            $table->dropIndex('idx_appreciation_id');
            $table->dropIndex('idx_checked');
        });

        // Suppression des tables
        Schema::dropIfExists('criterias');
        Schema::dropIfExists('appreciations');
        Schema::dropIfExists('evaluations');
    }
}
