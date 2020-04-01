<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('nickname', 64);
            $table->string('email', 128)->nullable()->comment('邮箱');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('password_salt', 16)->comment('密码加密的salt');
            $table->rememberToken();
            $table->string('phone', 20)->unique()->nullable()->comment('电话');
            $table->unsignedTinyInteger('gender')->default(2)->comment('0为女， 1为男，2未设定');
            $table->unsignedTinyInteger('login_type')->default(0)->comment('0为不使用第三方账号登录，1为github登录');
            $table->string('avatar')->nullable()->comment('头像');
            $table->string('title')->nullable()->comment('标题');
            $table->text('introduction')->nullable()->comment('个人简介');
            $table->json('carousel')->nullable()->comment('轮播图+跳转地址');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
