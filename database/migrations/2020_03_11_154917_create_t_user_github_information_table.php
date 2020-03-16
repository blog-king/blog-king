<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTUserGithubInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_user_github_information', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('github_id')->default(0);
            $table->string('name', 32)->unique();
            $table->string('nickname', 32);
            $table->string('email', 64)->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
            $table->index(['name'], 'idx_name');
            $table->index(['user_id'], 'idx_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_user_github_information');
    }
}
