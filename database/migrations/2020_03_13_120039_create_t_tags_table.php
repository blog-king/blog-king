<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_tags', function (Blueprint $table) {
            $table->id();
            $table->string("name", 32);
            $table->integer("type_id")->unsigned()->comment("tag的类型id");
            $table->timestamps();
            $table->index('type_id', 'idx_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_tags');
    }
}
