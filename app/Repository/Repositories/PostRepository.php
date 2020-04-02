<?php

namespace App\Repository\Repositories;

use App\Models\Post;
use App\Models\PostHistory;
use App\Models\PostTag;
use App\Models\Tag;
use App\Repository\Interfaces\PostInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class PostRepository implements PostInterface
{
    const TARGET_TYPE_TAG = 'tag'; //通过tag的方式获取列表
    const TARGET_TYPE_USER = 'user'; //通过userId的方式获取列表

    /**
     * 通过postId 获取一个文章，并保存到缓存里面，缓存一个小时，因为都为静态数据，动态数据已经分离.
     *
     * @param int $id
     *
     * @return Post|null
     */
    public function getPostById(int $id): ?Post
    {
        $cacheKey = $this->genPostCacheKeyById($id);

        return Cache::remember($cacheKey, now()->addHour(), function () use ($id) {
            $data = Post::query()->with('tags')->find($id);
            if ($data instanceof Post) {
                return $data;
            }

            return null;
        });
    }

    public function getPostsByIds(array $ids): Collection
    {
        $collection = Post::query()->with('tags')->whereIn('id', $ids)->get();
        $collection->each(function (Post $post) {
            if (Post::PRIVACY_PUBLIC == $post->privacy) {
                Cache::add($this->genPostCacheKeyById($post->id), $post, now()->addHour());
            }
        });

        return $collection;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     *
     * @param array $tagIds
     *
     * @return Post|null
     *
     * @param int   $userId
     * @param array $data
     *                      eg: ['title'=>require, content=> require, 'seo_words' => require, 'status' => require, 'privacy' => require,
     *                      'description' => ?, 'post_index' => ?],
     */
    public function create(int $userId, array $data, array $tagIds = []): ?Post
    {
        $post = Model::resolveConnection()->transaction(function () use ($userId, $data, $tagIds) {
            $post = new Post();
            $post->fill($data);
            $post->user_id = $userId;
            $post->save();

            if (!empty($tagIds)) {
                $now = date('Y-m-d H:i:s');
                $bulkInsetValues = Tag::query()
                    ->whereIn('id', $tagIds)
                    ->get()
                    ->map(function (Tag $tag) use ($post, $now) {
                        return [
                            'post_id' => $post->id,
                            'tag_id' => $tag->id,
                            'created_at' => $now,
                            'name' => $tag->name,
                            'user_id' => $post->user_id,
                        ];
                    })->toArray();
                PostTag::query()->insert($bulkInsetValues);
            }

            return $post;
        });
        //创建一篇文章后将缓存预热一次
        return $this->getPostById($post->id);
    }

    /**
     * @throws \Exception
     *
     * @param int $userId
     *
     * @return bool
     *
     * @param int $id
     */
    public function delete(int $id, int $userId): bool
    {
        //$post = $this->getPostById($id);
        $post = Post::query()->find($id);
        if (empty($post)) {
            throw new \Exception('Not Found.', 404);
        }

        if ($post instanceof Post && $post->user_id != $userId) {
            throw new \Exception('FORBIDDEN.', 403);
        }

        $post->delete();

        return true;
    }

    /**
     * 更新一个文章.
     *
     * @throws \Exception
     * @throws \Throwable
     *
     * @param array $data   eg: ['title' => xxx, 'content' => xxx, 'privacy' => 1]
     * @param array $tagIds eg: [1,2,3]
     *
     * @return bool
     *
     * @param int $id
     * @param int $userId
     */
    public function update(int $id, int $userId, array $data, array $tagIds): bool
    {
        /** @var Post $post */
        $post = Post::query()->find($id);

        if (empty($post)) {
            throw new \Exception('Not Found.', 404);
        }

        if ($post instanceof Post && $post->user_id != $userId) {
            throw new \Exception('FORBIDDEN.', 403);
        }

        return Model::resolveConnection()->transaction(function () use ($id, $userId, $data, $tagIds, $post) {
            //将文章写入历史记录保存
            $postHistory = new PostHistory();
            $postHistory->post_id = $post->id;
            $postHistory->title = $post->title;
            $postHistory->content = $post->content;
            $postHistory->save();

            $post->update($data);

            $post->postTags()->whereNotIn('tag_id', $tagIds)->delete();
            $diffIds = array_diff(
                $tagIds,
                $post->postTags()->select(['post_id', 'tag_id'])->get()->pluck('tag_id')->toArray()
            );

            if (!empty($diffIds)) {
                $now = date('Y-m-d H:i:s');

                $addTags = Tag::query()->whereIn('id', $diffIds)->get()
                    ->map(function (Tag $tag) use ($post, $now) {
                        return [
                            'post_id' => $post->id,
                            'tag_id' => $tag->id,
                            'created_at' => $now,
                            'name' => $tag->name,
                            'user_id' => $post->user_id,
                        ];
                    })->toArray();

                if (!empty($addTags)) {
                    PostTag::query()->insert($addTags);
                }
            }

            return true;
        });
    }

    /**
     * @param string $targetType eg: tag, user
     * @param int    $targetId
     * @param array  $options    ['limit' => int, 'page' => ?, 'user_id=> ?, 'next_id' => ?]
     *
     * @return array ['data' => ['Post', 'Post'], 'next' => bool]
     */
    public function getPosts(string $targetType, int $targetId, array $options = []): array
    {
        $limit = $options['limit'];
        $page = $options['page'];
        $next = true;
        $userId = $options['user_id'];
        switch ($targetType) {
            case self::TARGET_TYPE_TAG:
                while (true) {
                    $postIds = PostTag::query()
                        ->where(['tag_id' => $targetId])
                        ->take($limit + 1) //这里 +1 方便计算下一页
                        ->skip(($page - 1) * $limit)
                        ->orderByDesc('created_at')
                        ->pluck('post_id')
                        ->all();
                    $posts = $this->getPostsByIds($postIds);

                    $count = $posts->count();
                    $count <= $limit && $next = false;

                    $result = [];
                    $i = 0;
                    /** @var Post $post */
                    foreach ($posts as $post) {
                        if ($post->user_id == $userId && (Post::PRIVACY_HIDDEN == $post->privacy || Post::STATUS_DRAFT == $post->status)) {
                            continue;
                        }
                        if ($i == $limit) {
                            break;
                        }
                        ++$i;
                        $result[] = $post;
                    }
                    //拿够一页的数量则中断返回
                    if ($i >= $limit || $count <= $limit) {
                        return ['data' => $result, 'next' => $next];
                    }
                }
                break;
            case self::TARGET_TYPE_USER:
                $posts = Post::query()
                    ->with('tags')
                    ->where(['user_id' => $targetId])
                    ->when($targetId != $userId, function (Builder $query) {
                        $query->where(['privacy' => Post::PRIVACY_PUBLIC, 'status' => Post::STATUS_PUBLISH]);
                    })
                    ->take($limit + 1) //这里 +1 方便计算下一页
                    ->skip(($page - 1) * $limit)
                    ->orderByDesc('created_at')
                    ->get();

                $result = [];
                $posts->count() <= $limit && $next = false;
                $i = 0;
                foreach ($posts as $post) {
                    $result[] = $post;
                    ++$i;
                    if ($i >= $limit) {
                        break;
                    }
                }

                return ['data' => $result, 'next' => $next];
                break;
        }
    }

    /**
     * 通过 postTagId 获取文章列表.
     *
     * @param int $userId
     * @param $tags
     * @param int $page
     * @param int $perPage
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function getPostsByPostTags(int $userId, $tags, $page = 1, $perPage = 10)
    {
        $tags = is_array($tags) ? $tags : [$tags];

        return Post::query()
            ->visible()
            ->with('postTags')
            ->whereHas('postTags', function (HasMany $hasMany) use ($tags) {
                $hasMany->whereIn('tag_id', $tags);
            })
            ->where(['user_id' => $userId])
            ->simplePaginate($perPage, ['*'], 'page', $page);
    }

    /**
     * 通过 userId 获取文章列表.
     *
     * @param int $userId
     * @param int $page
     * @param int $perPage
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function getPostsByUser(int $userId, $page = 1, $perPage = 10)
    {
        return Post::query()
            ->visible()
            ->with('postTags')
            ->where(['user_id' => $userId])
            ->simplePaginate($perPage, ['*'], 'page', $page);
    }

    /**
     * 情报缓存的key.
     *
     * @param int $id
     *
     * @return string
     */
    public function genPostCacheKeyById(int $id): string
    {
        return "post-id:{$id}";
    }
}
