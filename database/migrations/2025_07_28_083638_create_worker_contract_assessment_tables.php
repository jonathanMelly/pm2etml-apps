<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table worker_contract_assessments avec la structure de ton modèle
        Schema::create('worker_contract_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_contract_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('class_id');
            $table->string('job_title');
            $table->string('status')->nullable();
            $table->timestamps();

            // Index avec noms courts
            $table->index('worker_contract_id', 'wca_worker_contract_id');
            $table->index('teacher_id', 'wca_teacher_id');
            $table->index('student_id', 'wca_student_id');
            $table->index('job_id', 'wca_job_id');
            $table->index('class_id', 'wca_class_id');
        });

        // Relation miroire dans contract_worker
        Schema::table('contract_worker', function (Blueprint $table) {
            $table->unsignedBigInteger('worker_contract_assessment_id')->nullable()->after('id');
            $table->index('worker_contract_assessment_id', 'cw_assessment_id');
        });

        Schema::create('assessment_criterion_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();

            // Index unique
            $table->unique('name', 'acc_name_unique');
        });

        Schema::create('assessment_criterion_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('assessment_criterion_category_id');
            $table->integer('position');
            $table->text('description');
            $table->timestamps();
            $table->unsignedBigInteger('user_id')->nullable();

            // Index avec noms courts
            $table->index('assessment_criterion_category_id', 'act_category_id');
            $table->index('user_id', 'act_user_id');
            $table->index('position', 'act_position');
        });

        // Table assessments pour stocker les appréciations
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_contract_assessment_id');
            $table->dateTime('date');
            $table->string('timing'); // auto80, eval80, etc.
            $table->text('student_remark')->nullable();
            $table->timestamps();

            // Index avec noms courts
            $table->index('worker_contract_assessment_id', 'assessments_wca_id');
            $table->index('date', 'assessments_date');
            $table->index('timing', 'assessments_timing');
        });

        // Table assessment_criteria pour les critères détaillés
        Schema::create('assessment_criteria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->string('timing'); // auto80, inter80, etc.
            $table->unsignedBigInteger('template_id');
            $table->unsignedTinyInteger('value')->default(0); // 0, 1, 2, 3
            $table->boolean('checked')->default(false);
            $table->text('remark_criteria')->nullable();
            $table->unsignedTinyInteger('position')->default(1);
            $table->timestamps();

            // Index avec noms courts
            $table->index('assessment_id', 'ac_assessment_id');
            $table->index('template_id', 'ac_template_id');
            $table->index('timing', 'ac_timing');
            $table->index('value', 'ac_value');
        });

    // Ajout des clés étrangères séparément avec des noms courts
        Schema::table('worker_contract_assessments', function (Blueprint $table) {
            $table->foreign('worker_contract_id', 'wca_worker_contract_fk')
                  ->references('id')->on('contract_worker')->onDelete('cascade');
            $table->foreign('teacher_id', 'wca_teacher_fk')
                  ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('student_id', 'wca_student_fk')
                  ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('job_id', 'wca_job_fk')
                  ->references('id')->on('job_definitions')->onDelete('cascade');
            $table->foreign('class_id', 'wca_class_fk')
                  ->references('id')->on('group_names')->onDelete('cascade');
        });

        Schema::table('assessment_criterion_templates', function (Blueprint $table) {
            $table->foreign('assessment_criterion_category_id', 'act_category_fk')
                  ->references('id')->on('assessment_criterion_categories')->onDelete('cascade');
            $table->foreign('user_id', 'act_user_fk')
                  ->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->foreign('worker_contract_assessment_id', 'assessments_wca_fk')
                  ->references('id')->on('worker_contract_assessments')->onDelete('cascade');
        });

        Schema::table('assessment_criteria', function (Blueprint $table) {
            $table->foreign('assessment_id', 'ac_assessment_fk')
                  ->references('id')->on('assessments')->onDelete('cascade');
            $table->foreign('template_id', 'ac_template_fk')
                  ->references('id')->on('assessment_criterion_templates')->onDelete('cascade');
        });

        // Création de la permission si elle n'existe pas
        if (!Permission::where('name', 'contract.assess')->exists()) {
            Permission::create(['name' => 'contract.assess']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression de la permission
        Permission::where('name', 'contract.assess')->delete();

         // Suppression de la relation miroire
        Schema::table('contract_worker', function (Blueprint $table) {
            $table->dropForeign('cw_assessment_fk');
            $table->dropIndex('cw_assessment_id');
            $table->dropColumn('worker_contract_assessment_id');
        });

        Schema::dropIfExists('assessment_criteria');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('assessment_criterion_templates');
        Schema::dropIfExists('assessment_criterion_categories');
        Schema::dropIfExists('worker_contract_assessments');
    }
};