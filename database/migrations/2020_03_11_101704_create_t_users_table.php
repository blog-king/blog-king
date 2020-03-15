<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string("email", 128)->nullable()->comment("邮箱");
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('password_salt', 16)->comment("密码加密的salt");
            $table->rememberToken();
            //phone 用上default 默认值，为了更方便索引的建立与查询，利用string，方便输入区号+86等，方便like查询，没有人会用电话号码做数学运算
            $table->string("phone", 20)->default("")->comment("电话");

            $table->tinyInteger('sex')->default(2)->comment("0为女， 1为男，2未设定");
            $table->tinyInteger('login_type')->default(0)->comment("0为不使用第三方账号登录，1为github登录");
            $table->string("avatar")->nullable()->comment("头像");
            $table->text("introduction")->nullable()->comment("个人简介");

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
        Schema::dropIfExists('t_users');
    }
}
