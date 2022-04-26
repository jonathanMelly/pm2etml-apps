<?php

use App\Enums\ContractStatus;
use App\Enums\CustomPivotTableNames;
use App\Models\Contract;
use App\Models\GroupMember;
use App\Models\JobDefinition;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tables()[0], function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedTinyInteger("status")->default(ContractStatus::REGISTERED->value);
            $table->dateTime('status_timestamp')->default(now());
            $table->dateTime('start');
            $table->dateTime('end');
            $table->boolean('success')
                ->default(false)
                ->comment('True if the work has been approved by the client');
            $table->foreignIdFor(JobDefinition::class)->constrained();
            $table->softDeletes();
        });

        //worker
        Schema::create($this->tables()[1], function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $uniques[]=$table->foreignIdFor(Contract::class);
            $uniques[]=$table->foreignIdFor(GroupMember::class)->constrained();

            collect($uniques)->each(fn($foreign)=>$foreign->constrained()->cascadeOnDelete()->cascadeOnUpdate());

            //Only 1 role allowed for now
            $table->unique(collect($uniques)->pluck('name')->toArray());
        });

        //client
        Schema::create($this->tables()[2], function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $uniques[]=$table->foreignIdFor(Contract::class);
            $uniques[]=$table->foreignIdFor(User::class);

            collect($uniques)->each(fn($foreign)=>$foreign->constrained()->cascadeOnDelete()->cascadeOnUpdate());


            //Only 1 role allowed for now
            $table->unique(collect($uniques)->pluck('name')->toArray());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        collect($this->tables())->each(
            fn($table) => Schema::dropIfExists($table)
        );
    }

    public function tables():array
    {
        return [
            app(Contract::class)->getTable(),
            CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value,
            CustomPivotTableNames::CONTRACT_USER->value,
        ];
    }
};
