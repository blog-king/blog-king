<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTPostTagMapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_post_tag_map', function (Blueprint $table) {
            $table->id();
            $table->integer("post_id");
            $table->integer("tag_id");
            $table->timestamps();
            $table->index(['tag_id', 'post_id'], 'idx_post_tag');
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
        Schema::dropIfExists('t_post_tag_map');
    }
}
