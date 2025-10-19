<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('worker_contract_day_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_contract_id');
            $table->date('date');
            $table->unsignedTinyInteger('periods_count'); // 1..6
            $table->unsignedTinyInteger('precision_minutes'); // 5,10,15
            $table->json('periods')->nullable(); // array of {index, chunks, minutes, note}
            $table->text('student_notes')->nullable();
            $table->text('appreciation')->nullable(); // teacher appreciation
            $table->timestamps();

            $table->unique(['worker_contract_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_contract_day_logs');
    }
};

