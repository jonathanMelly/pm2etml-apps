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
        \Spatie\Permission\Models\Permission::create(['name' => 'evaluation.storeEvaluation']);

        // Table users
        Schema::table('users', function (Blueprint $table) {
            // Index sur la colonne email
            $table->index('email', 'idx_email');
        });

        // Table role_has_permissions
        Schema::table('role_has_permissions', function (Blueprint $table) {
            // Index sur la colonne permission_id
            $table->index('permission_id', 'idx_permission_id');
        });

        // Table evaluations
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->string('project_name');
            $table->text('student_remark')->nullable();
            $table->timestamps();

            // Ajout des index
            $table->index('evaluator_id', 'idx_evaluator_id');
            $table->index('student_id', 'idx_student_id');
            $table->index('project_name', 'idx_project_name'); // Index pour le nom du projet
        });

        // Table appreciations
        Schema::create('appreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
            $table->date('date');
            $table->timestamps();

            // Ajout des index
            $table->index('evaluation_id', 'idx_evaluation_id');
            $table->index('date', 'idx_date'); // Index pour la date
        });

        // Table criteria
        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('level');
            $table->foreignId('appreciation_id')->constrained('appreciations')->onDelete('cascade');
            $table->string('name');
            $table->tinyInteger('value');
            $table->boolean('checked')->default(false);
            $table->timestamps();

            // Ajout des index
            $table->index('appreciation_id', 'idx_appreciation_id');
            $table->index('checked', 'idx_checked'); // Index pour la colonne 'checked'
        });
    }

    /**
     * Annuler les migrations.
     *
     * @return void
     */
    public function down()
    {
        // Supprime la permission en cas de rollback
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
        });

        Schema::table('appreciations', function (Blueprint $table) {
            $table->dropIndex('idx_evaluation_id');
            $table->dropIndex('idx_date');
        });

        Schema::table('criteria', function (Blueprint $table) {
            $table->dropIndex('idx_appreciation_id');
            $table->dropIndex('idx_checked');
        });

        // Suppression des tables
        Schema::dropIfExists('criteria');
        Schema::dropIfExists('appreciations');
        Schema::dropIfExists('evaluations');
    }
}
