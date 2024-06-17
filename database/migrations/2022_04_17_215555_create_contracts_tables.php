<?php

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
     */
    public function up(): void
    {
        Schema::create($this->tables()[0], function (Blueprint $table) {
            $table->id();

            $table->dateTime('start');
            $table->dateTime('end');

            $table->dateTime('success_date')
                ->nullable()->default(null)
                ->comment('last date of success field change, null=not evaluated');

            $table->boolean('success')
                ->default(false)
                ->comment('True if the work has been approved by the client');
            $table->string('success_comment')->nullable();

            $table->foreignIdFor(JobDefinition::class)->constrained();

            $table->timestamps();
            $table->softDeletes();
        });

        //worker
        Schema::create($this->tables()[1], function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $uniques[] = $table->foreignIdFor(Contract::class);
            $uniques[] = $table->foreignIdFor(GroupMember::class);

            collect($uniques)->each(fn ($foreign) => $foreign->constrained()->cascadeOnDelete()->cascadeOnUpdate());

            //Only 1 role allowed for now
            $table->unique(collect($uniques)->pluck('name')->toArray());
        });

        //client
        Schema::create($this->tables()[2], function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $uniques[] = $table->foreignIdFor(Contract::class);
            $uniques[] = $table->foreignIdFor(User::class);

            collect($uniques)->each(fn ($foreign) => $foreign->constrained()->cascadeOnDelete()->cascadeOnUpdate());

            //Only 1 role allowed for now
            $table->unique(collect($uniques)->pluck('name')->toArray());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        collect($this->tables())->each(
            fn ($table) => Schema::dropIfExists($table)
        );
    }

    public function tables(): array
    {
        return [
            app(Contract::class)->getTable(),
            CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value,
            CustomPivotTableNames::CONTRACT_USER->value,
        ];
    }
};
