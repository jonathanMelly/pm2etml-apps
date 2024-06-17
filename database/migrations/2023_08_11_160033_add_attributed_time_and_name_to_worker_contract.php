<?php

use App\Enums\CustomPivotTableNames;
use App\Models\WorkerContract;
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
        Schema::table(CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value, function (Blueprint $table) {
            $table->unsignedInteger('allocated_time')->nullable()->default(null);
            $table->unsignedInteger('allocated_time_unit')->default(\App\Enums\RequiredTimeUnit::PERIOD->value);
            $table->string('name')->comment('If not empty, custom part name of job...')
                ->nullable()
                ->default(null);
        });

        //done in prod, no need anymore... worse it makes crash further migrations
        //        //Fill new fields of existing contracts with project values
        //        WorkerContract::with('contract.jobDefinition')->each(function($wc){
        //            /* @var $wc WorkerContract */
        //            if($wc->allocated_time===null && $wc->contract!==null){
        //               $job = $wc->contract->jobDefinition;
        //               $wc->allocated_time = $job->allocated_time;
        //               $wc->allocated_time_unit = $job->allocated_time_unit;
        //               $wc->name="";
        //
        //               $wc->save();
        //           }
        //        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value, function (Blueprint $table) {
            $table->dropColumn('allocated_time');
            $table->dropColumn('allocated_time_unit');
        });
    }
};
