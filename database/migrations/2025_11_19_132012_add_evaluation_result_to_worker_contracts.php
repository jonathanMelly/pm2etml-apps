<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = \App\Enums\CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value;
        $logTableName = tbl(\App\Models\WorkerContractEvaluationLog::class);

        // Step 1: Drop trigger to avoid it firing during migration
        DB::unprepared('DROP TRIGGER IF EXISTS contract_worker_eval_log');

        // Step 2: Rename and change log table columns from boolean to varchar (to support both old 0/1 and new na/pa/a/la)
        // Use Schema builder which handles both MySQL and SQLite
        Schema::table($logTableName, function (Blueprint $table) {
            $table->string('old_result', 10)->nullable()->after('contract_id');
            $table->string('new_result', 10)->nullable()->after('old_result');
        });

        // Copy data from old columns to new columns
        DB::statement("UPDATE {$logTableName} SET old_result = old_success, new_result = new_success");

        // Drop old columns
        Schema::table($logTableName, function (Blueprint $table) {
            $table->dropColumn(['old_success', 'new_success']);
        });

        // Step 3: Add new evaluation_result column (nullable)
        // Use Schema builder for cross-database compatibility
        Schema::table($tableName, function (Blueprint $table) {
            $table->enum('evaluation_result', ['na', 'pa', 'a', 'la'])
                ->nullable()
                ->comment('Evaluation result: na=non acquis, pa=partiellement acquis, a=acquis, la=largement acquis');
        });

        // Step 4: Migrate existing data: convert boolean success to evaluation_result
        DB::statement("
            UPDATE {$tableName}
            SET evaluation_result = CASE
                WHEN success = 1 THEN 'a'
                WHEN success = 0 THEN 'na'
                ELSE NULL
            END
            WHERE success IS NOT NULL
        ");

        // Step 5: Drop the old success column
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('success');
        });

        // Step 6: Recreate trigger to use evaluation_result
        $mysqlConditionStart = '';
        $mysqlConditionEnd = '';
        $sqliteCondition = 'WHEN NEW.evaluation_result IS NOT NULL';
        if (! app()->environment('testing', 'litedb')) {
            $sqliteCondition = '';
            $mysqlConditionStart = 'IF NEW.evaluation_result IS NOT NULL THEN';
            $mysqlConditionEnd = 'END IF;';
        }

        DB::unprepared(
            'CREATE TRIGGER contract_worker_eval_log AFTER UPDATE ON '.$tableName."
                FOR EACH ROW $sqliteCondition
                BEGIN
                    $mysqlConditionStart
                        INSERT INTO {$logTableName}
                            (contract_id,
                            old_result,
                            new_result,
                            old_comment,
                            new_comment,
                            old_date,
                            new_date)
                        values
                            (NEW.contract_id,
                            OLD.evaluation_result,
                            NEW.evaluation_result,
                            OLD.success_comment,
                            NEW.success_comment,
                            OLD.success_date,
                            NEW.success_date);
                    $mysqlConditionEnd
                END"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = \App\Enums\CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value;
        $logTableName = tbl(\App\Models\WorkerContractEvaluationLog::class);

        // Step 1: Drop trigger
        DB::unprepared('DROP TRIGGER IF EXISTS contract_worker_eval_log');

        // Step 2: Re-add success column
        Schema::table($tableName, function (Blueprint $table) {
            $table->boolean('success')
                ->nullable()
                ->after('success_date')
                ->comment('True if the work has been approved by the client');
        });

        // Step 3: Convert evaluation_result back to boolean success
        DB::statement("
            UPDATE {$tableName}
            SET success = CASE
                WHEN evaluation_result IN ('a', 'la') THEN 1
                WHEN evaluation_result IN ('na', 'pa') THEN 0
                ELSE NULL
            END
        ");

        // Step 4: Drop evaluation_result column
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('evaluation_result');
        });

        // Step 5: Convert log table varchar columns back to boolean and rename
        // Add old columns back
        Schema::table($logTableName, function (Blueprint $table) {
            $table->boolean('old_success')->nullable()->after('contract_id');
            $table->boolean('new_success')->nullable()->after('old_success');
        });

        // Convert and copy data
        DB::statement("
            UPDATE {$logTableName}
            SET
                old_success = CASE old_result WHEN 'a' THEN 1 WHEN 'la' THEN 1 WHEN 'na' THEN 0 WHEN 'pa' THEN 0 ELSE old_result END,
                new_success = CASE new_result WHEN 'a' THEN 1 WHEN 'la' THEN 1 WHEN 'na' THEN 0 WHEN 'pa' THEN 0 ELSE new_result END
        ");

        // Drop new columns
        Schema::table($logTableName, function (Blueprint $table) {
            $table->dropColumn(['old_result', 'new_result']);
        });

        // Step 6: Recreate original trigger
        $mysqlConditionStart = '';
        $mysqlConditionEnd = '';
        $sqliteCondition = 'WHEN NEW.success IS NOT NULL';
        if (! app()->environment('testing', 'litedb')) {
            $sqliteCondition = '';
            $mysqlConditionStart = 'IF NEW.success IS NOT NULL THEN';
            $mysqlConditionEnd = 'END IF;';
        }

        DB::unprepared(
            'CREATE TRIGGER contract_worker_eval_log AFTER UPDATE ON '.$tableName."
                FOR EACH ROW $sqliteCondition
                BEGIN
                    $mysqlConditionStart
                        INSERT INTO {$logTableName}
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
                END"
        );
    }
};
