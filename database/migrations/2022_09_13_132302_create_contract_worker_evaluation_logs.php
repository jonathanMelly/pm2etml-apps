<?php

use App\Models\WorkerContract;
use App\Models\WorkerContractEvaluationLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TRIGGER = 'contract_worker_eval_log';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(tbl(WorkerContractEvaluationLog::class), function (Blueprint $table) {
            //
            $table->id();

            $table->foreignIdFor(\App\Models\Contract::class)->constrained();

            $table->dateTime('created_at')->useCurrent();

            $table->boolean('old_success')->nullable();
            $table->boolean('new_success');

            $table->string('old_comment')->nullable();
            $table->string('new_comment')->nullable();

            $table->dateTime('old_date')->nullable();
            $table->dateTime('new_date');

            $table->dateTime('reported_at')->nullable();

        });

        //As trigger syntax is different on sqlite (tests) and mariadb and
        //there don’t seem to need conditions (only updates should be for success...), the trigger is left SIMPLE !
        DB::unprepared(
            'CREATE TRIGGER '.self::TRIGGER.' AFTER UPDATE ON '.tbl(WorkerContract::class).'
                    FOR EACH ROW
                    BEGIN
                        INSERT INTO '.tbl(WorkerContractEvaluationLog::class).'
                            (contract_id,
                            old_success,
                            new_success,
                            old_comment,
                            new_comment,
                            old_date,
                            new_date)
                        values
                            (NEW.contract_id,
                            OLD.success,
                            NEW.success,
                            OLD.success_comment,
                            NEW.success_comment,
                            OLD.success_date,
                            NEW.success_date);

                    END');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(tbl(WorkerContractEvaluationLog::class), function (Blueprint $table) {
            $table->drop();
        });

        DB::unprepared('DROP TRIGGER '.self::TRIGGER);
    }
};
