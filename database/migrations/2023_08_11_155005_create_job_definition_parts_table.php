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
        Schema::create($this->table(), function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\JobDefinition::class);
            $table->string('name');
            $table->unsignedInteger('allocated_time')->nullable()->default(null);
            $table->unsignedInteger('allocated_time_unit')->default(\App\Enums\RequiredTimeUnit::PERIOD->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->table());
    }

    public function table(): string
    {
        return app(\App\Models\JobDefinitionPart::class)->getTable();
    }
};
