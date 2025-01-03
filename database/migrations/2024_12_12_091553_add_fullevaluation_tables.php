<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        \Spatie\Permission\Models\Permission::create(['name' => 'evaluation.storeEvaluation']);

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

        Schema::create('appreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
            $table->date('date');
            $table->tinyInteger('level')->unsigned();
            $table->timestamps();

            $table->index('date');
        });

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

    /**
     * Annuler les migrations.
     *
     * @return void
     */
    public function down()
    {
        \Spatie\Permission\Models\Permission::where('name', 'evaluation.storeEvaluation')->delete();

        Schema::dropIfExists('criterias');
        Schema::dropIfExists('appreciations');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('default_criterias');
    }
};
