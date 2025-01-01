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
        Schema::table("job_definitions", function (Blueprint $table) {
            $table->boolean('by_application')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("job_definitions", function (Blueprint $table) {
            $table->dropColumn('by_application');
        });
    }
};
