<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTPostHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_post_history', function (Blueprint $table) {
            $table->id();
            $table->integer('post_id')->unsigned()->comment('文章id');
            $table->string('title');
            $table->text('content');
            $table->timestamp('created_at');
            $table->index('post_id', 'idx_post_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_post_history');
    }
}
