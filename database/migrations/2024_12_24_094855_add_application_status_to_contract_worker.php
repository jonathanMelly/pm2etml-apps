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
        Schema::table("contract_worker", function (Blueprint $table) {
            // Application status of 0 means application complete (the guy is hired for the job)
            // above 0, it is the priority of the worker's wish, 1 is the highest priority
            $table->unsignedInteger('application_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("contract_worker", function (Blueprint $table) {
            $table->dropColumn('application_status');
        });
    }
};
