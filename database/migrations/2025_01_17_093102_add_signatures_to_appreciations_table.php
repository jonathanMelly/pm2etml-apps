<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;



class AddSignaturesToAppreciationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ajouter la colonne "signatures" à la table "appreciations"
        Schema::table('appreciations', function (Blueprint $table) {
            $table->json('signatures')->nullable();  // La colonne "signatures" qui sera un tableau JSON
        });

        // Vérifier si la permission existe avant de tenter de la créer
        if (!Permission::where('name', 'evaluation.storeEvaluation')->exists()) {
            Permission::create(['name' => 'evaluation.storeEvaluation', 'guard_name' => 'web']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer la colonne "signatures"
        Schema::table('appreciations', function (Blueprint $table) {
            $table->dropColumn('signatures');
        });

        // Supprimer la permission si elle existe
        Permission::where('name', 'evaluation.storeEvaluation')->delete();
    }
}
