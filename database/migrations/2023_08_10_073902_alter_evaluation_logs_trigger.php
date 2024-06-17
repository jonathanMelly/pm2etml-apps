<?php

use App\Models\WorkerContract;
use App\Models\WorkerContractEvaluationLog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const TRIGGER = 'contract_worker_eval_log';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER '.self::TRIGGER);

        //Trigger conditional format is not the same with sqlite and mysql
        $mysqlConditionStart = '';
        $mysqlConditionEnd = '';
        $sqliteCondition = 'WHEN NEW.success IS NOT NULL';
        if (! app()->environment('testing', 'litedb')) {
            $sqliteCondition = '';
            $mysqlConditionStart = 'IF NEW.success != NULL THEN';
            $mysqlConditionEnd = 'END IF;';
        }

        //As trigger syntax is different on sqlite (tests) and mariadb and
        //there don’t seem to need conditions (only updates should be for success...), the trigger is left SIMPLE !
        DB::unprepared(
            'CREATE TRIGGER '.self::TRIGGER.' AFTER UPDATE ON '.tbl(WorkerContract::class)."
                    FOR EACH ROW $sqliteCondition
                    BEGIN
                        $mysqlConditionStart
                            INSERT INTO ".tbl(WorkerContractEvaluationLog::class)."
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
                        $mysqlConditionEnd
                    END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER '.self::TRIGGER);
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
};
