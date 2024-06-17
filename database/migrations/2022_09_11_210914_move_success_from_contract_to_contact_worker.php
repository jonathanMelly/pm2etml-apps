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
        Schema::table(tbl(\App\Models\WorkerContract::class), function (Blueprint $table) {

            $table->dateTime('success_date')
                ->nullable()
                ->comment('last date of success field change');

            $table->boolean('success')
                ->nullable()
                ->comment('True if the work has been approved by the client');

            $table->string('success_comment')
                ->nullable();
        });

        //as there is not yet data on prod, no need to transfer ;-)

        foreach (['success_date', 'success', 'success_comment'] as $drop) {
            Schema::table(tbl(\App\Models\Contract::class), function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(tbl(\App\Models\Contract::class), function (Blueprint $table) {
            $table->dateTime('success_date')
                ->nullable()->default(null)
                ->comment('last date of success field change, null=not evaluated');

            $table->boolean('success')
                ->default(false)
                ->comment('True if the work has been approved by the client');
            $table->string('success_comment')->nullable();
        });

        foreach (['success_date', 'success', 'success_comment'] as $drop) {
            Schema::table(tbl(\App\Models\WorkerContract::class), function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }

    }
};
