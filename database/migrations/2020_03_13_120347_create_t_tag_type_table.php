<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTTagTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_tag_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32);
            $table->integer('level')->default(0)->comment("类型的级别");
            $table->integer("parent_id")->nullable()->comment("tag的父类");
            $table->timestamp("created_at");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_tag_type');
    }
}
