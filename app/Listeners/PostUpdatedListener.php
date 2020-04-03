<?php

namespace App\Listeners;

use App\Events\PostUpdated;
use App\Models\PostHistory;

class PostUpdatedListener
{
    public function handle(PostUpdated $event)
    {
        $post = $event->getPost();
        //将文章写入历史记录保存
        $postHistory = new PostHistory();
        $postHistory->post_id = $post->id;
        $postHistory->title = $post->title;
        $postHistory->content = $post->content;
        $postHistory->save();
    }
}
