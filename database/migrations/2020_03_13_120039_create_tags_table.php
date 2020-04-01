<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32);
            $table->integer('level')->default(0)->comment('层级关系，第几层');
            $table->integer('parent_id')->default(0)->comment('tag的父类id,默认第一层为0');
            $table->timestamps();
            $table->index(['parent_id', 'level'], 'idx_parent_level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}
