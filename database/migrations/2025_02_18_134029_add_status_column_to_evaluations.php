<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->string('status')->default('not_evaluated');
            // Ajoutez un statut par défaut si nécessaire
        });
    }

    public function down()
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
