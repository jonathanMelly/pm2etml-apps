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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();

            $uniques[] = $table->foreignIdFor(\App\Models\GroupName::class);
            $uniques[] = $table->foreignIdFor(\App\Models\AcademicPeriod::class);

            collect($uniques)->each(fn ($foreign) => $foreign->constrained());

            $table->unique(collect($uniques)->pluck('name')->toArray());

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
