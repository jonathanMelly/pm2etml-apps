<?php

use App\Enums\ContractRole;
use App\Enums\ContractStatus;
use App\Models\JobDefinition;
use App\Models\Contract;
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
        Schema::create($this->table(), function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedTinyInteger("status")->default(ContractStatus::REGISTERED->value);
            $table->dateTime('status_timestamp')->default(now());
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->foreignIdFor(JobDefinition::class);
            $table->softDeletes();
        });

        Schema::create('contract_user', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Contract::class);
            $table->foreignIdFor(User::class);

            //See App\Enums\ContractRole for details
            $table->unsignedTinyInteger("role");

            //Only 1 role allowed for now
            $table->unique(array('contract_id', 'user_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table());
    }

    public function table():string
    {
        return app(Contract::class)->getTable();
    }
};
