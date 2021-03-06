<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('title')->comment('标题');
            $table->string('description')->comment('描述');
            $table->string('thumbnail')->nullable()->comment('缩略图');
            $table->string('seo_words')->comment('用作于seo的词');
            $table->text('post_index')->nullable()->comment('文章目录');
            $table->mediumText('content')->comment('内容');
            $table->tinyInteger('status')->default(1)->comment('发布状态，1位发布，2为草稿');
            $table->integer('sort')->default(1)->comment('排序，越大越靠前');
            $table->dateTime('published_at')->index()->default(new \Illuminate\Database\Query\Expression('now()'))->comment('发布时间');
            $table->tinyInteger('privacy')->default(1)->comment('权限，1为公开，2为仅自己可见');
            $table->integer('commented_count')->default(0)->comment('评论数量');
            $table->integer('liked_count')->default(0)->comment('点赞数量');
            $table->integer('bookmarked_count')->default(0)->comment('收藏数量');
            $table->integer('viewed_count')->default(0)->comment('收藏数量');
            $table->softDeletes();
            $table->timestamps();
            $table->index('user_id', 'idx_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
