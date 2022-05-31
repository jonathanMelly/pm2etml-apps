<?php

use App\Models\Attachment;
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
            $table->string('name');
            $table->string('storage_path');

            //Max 4G filesize
            $table->unsignedMediumInteger('size');

            $table->nullableMorphs('attachable');
            $table->string('type')->nullable();

            //TODO trigger on insert to guarantee that for attachable_type=image, only 1 attachable_id...??

            $table->softDeletes();
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
        return app(Attachment::class)->getTable();
    }
};
