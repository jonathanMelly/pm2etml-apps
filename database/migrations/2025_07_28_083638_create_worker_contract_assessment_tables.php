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
        //Pour stocker une éval sur un projet : par exemple à 80% l'élève met NA (cumulé sur les critères) => timing 0
        Schema::create(tbl(\App\Models\WorkerContractAssessment::class), function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_contract_id')
                ->constrained(tbl(\App\Models\WorkerContract::class));

            $table->string('timing'); // auto80, eval80, etc.

            //Copié/collé du résultat final du prof (eval100 du prof des asssessments)
            $table->string('result')->comment("na,pa,a,la");

            $table->text('comment')->nullable();

            //État du parcours de l'évaluation => TODO peut-on le calculer SINON il faut le pousser dans WorkerContract

            //$table->string('status')->nullable();
            $table->timestamps();

        });

        // Relation miroire dans contract_worker
        Schema::table(tbl(\App\Models\WorkerContract::class), function (Blueprint $table) {
            $table->foreignIdFor(App\Models\WorkerContractAssessment::class);
        });


        Schema::create(tbl(\App\Models\AssessmentCriterionCategory::class), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();

            // Index unique
            $table->unique('name', 'acc_name_unique');
        });

        Schema::create(tbl(\App\Models\AssessmentCriterionTemplate::class), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignIdFor(\App\Models\AssessmentCriterionCategory::class)
                ->constrained()
                ->name('fk_criterion_templates_category');
            $table->integer('position');
            $table->text('description');
            $table->timestamps();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->unique(['user_id', 'name','assessment_criterion_category_id'],'unique_template_user_name_category');
            $table->unique(['user_id', 'position','assessment_criterion_category_id'],'unique_template_user_position_category');
        });

        // Table assessment_criteria pour les critères détaillés
        Schema::create(tbl(\App\Models\AssessmentCriterion::class), function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\WorkerContractAssessment::class)->constrained();
            $table->foreignIdFor(\App\Models\AssessmentCriterionTemplate::class)->constrained();

            $table->string('result')->comment("na,pa,a,la");
            $table->boolean('active')->default(false);//Les profs peuvent ne pas évaluer des critères
            $table->text('comment')->nullable();

            $table->unsignedTinyInteger('position')->default(1);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(tbl(\App\Models\WorkerContract::class), function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\WorkerContractAssessment::class);
        });

        Schema::dropIfExists(tbl(\App\Models\WorkerContractAssessment::class));
        Schema::dropIfExists(tbl(\App\Models\AssessmentCriterion::class));
        Schema::dropIfExists(tbl(\App\Models\AssessmentCriterionTemplate::class));
        Schema::dropIfExists(tbl(\App\Models\AssessmentCriterionCategory::class));
    }
};
