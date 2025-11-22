<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('evaluation_versions', function (Blueprint $table) {
            $table->enum('evaluator_type', ['teacher', 'student'])->after('version_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluation_versions', function (Blueprint $table) {
            $table->dropColumn('evaluator_type');
        });
    }
};
