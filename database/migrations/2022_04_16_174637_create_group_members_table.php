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
        Schema::create(\App\Enums\CustomPivotTableNames::GROUP_USER->value, function (Blueprint $table) {
            $table->id();

            $uniques = [];
            $uniques[] = $table->foreignIdFor(\App\Models\User::class);
            $uniques[] = $table->foreignIdFor(\App\Models\Group::class);

            collect($uniques)->each(fn ($foreign) => $foreign->constrained());

            $table->unsignedTinyInteger('type')->default(0);
            $table->timestamps();
            $table->softDeletes();

            //One user (teacher or student) is unique in the group
            $table->unique(collect($uniques)->pluck('name')->toArray());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(\App\Enums\CustomPivotTableNames::GROUP_USER->value);
    }
};
