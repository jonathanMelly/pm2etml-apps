<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefaultCriteriasTable extends Migration
{
    public function up()
    {
        Schema::create('default_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->text('description');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->onUpdate(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedBigInteger('user_id')->nullable(); // Permet les valeurs nulles pour les critères par défaut
            $table->integer('position'); // Ajout de la colonne position
            $table->primary('id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('default_criterias');
    }
}
