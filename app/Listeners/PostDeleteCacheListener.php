<?php

namespace App\Listeners;

use App\Events\PostDeleted;
use App\Events\PostUpdated;
use App\Repository\Repositories\PostRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class PostDeleteCacheListener
{

    private PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        //文章更新或者删除，需要清除掉文章当前的缓存
        if ($event instanceof PostUpdated || $event instanceof PostDeleted) {
            $post = $event->getPost();
            $postId = $post->id;
            $cacheKey = $this->postRepository->genPostCacheKeyById($postId);
            Cache::forget($cacheKey);
        }
    }
}
