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
        Schema::table(\App\Enums\CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value, function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(\App\Enums\CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value, function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
