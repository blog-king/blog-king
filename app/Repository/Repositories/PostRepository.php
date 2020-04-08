<?php

namespace App\Repository\Repositories;

use App\Exceptions\ForbiddenException;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Tag;
use App\Repository\Interfaces\PostInterface;
use App\User;
use Illuminate\Database\Eloquent\Collection;
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

    public function getPostsByIds(array $ids, bool $filterUnvisible = true): Collection
    {
        $collection = Post::query()
            ->with('tags')
            ->when($filterUnvisible, function ($query) {
                /* @var Post $query */
                $query->visible();
            })
            ->whereIn('id', $ids)
            ->get();
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
        $post = new Post();
        $post->fill($data);
        $post->user_id = $userId;
        $post->save();

        $this->syncTags($post, $tagIds, true);

        return $post;
    }

    /**
     * @param User $user
     * @param int  $id
     *
     * @return bool
     */
    public function delete(User $user, int $id): bool
    {
        /** @var Post $post */
        $post = Post::query()->findOrFail($id);

        if ($user->can('delete', $post)) {
            $post->delete();

            return true;
        }

        throw new ForbiddenException(__('post.403_not_your_post'));
    }

    /**
     * 更新一个文章.
     *
     * @throws
     * @throws \App\Exceptions\ForbiddenException
     *
     * @param array $data   eg: ['title' => xxx, 'content' => xxx, 'privacy' => 1]
     * @param array $tagIds eg: [1,2,3]
     *
     * @return bool
     *
     * @param User $user
     * @param int  $id
     */
    public function update(User $user, int $id, array $data, array $tagIds): bool
    {
        /** @var Post $post */
        $post = Post::query()->findOrFail($id);

        if ($user->can('update', $post)) {
            $post->update($data);
            $this->syncTags($post, $tagIds);

            return true;
        }

        throw new ForbiddenException(__('post.403_not_your_post'));
    }

    /**
     * 同步文章标签.
     *
     * @param \App\Models\Post $post
     * @param array            $tagIds
     * @param bool             $create
     */
    public function syncTags(Post $post, array $tagIds, bool $create = false)
    {
        if ($create) {
            $diffIds = $tagIds;
        } else {
            // 把多余的 tags 删掉
            $post->postTags()->whereNotIn('tag_id', $tagIds)->delete();
            $diffIds = array_diff(
                $tagIds,
                $post->postTags()->select(['post_id', 'tag_id'])->get()->pluck('tag_id')->toArray()
            ); // 看看 tags 的 diff
        }

        if (!empty($diffIds)) { // 如果 tags 还有区别，就把缺少的补上去
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
            ->orderByDesc('sort')
            ->orderByDesc('published_at')
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
