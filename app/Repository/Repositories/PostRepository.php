<?php

namespace App\Repository\Repositories;

use App\Http\Requests\Post;
use App\Models\PostHistory;
use App\Models\Posts;
use App\Models\PostTagMap;
use App\Repository\Interfaces\PostInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostRepository implements PostInterface
{
    const TARGET_TYPE_TAG = 'tag'; //通过tag的方式获取列表
    const TARGET_TYPE_USER = 'user'; //通过userId的方式获取列表

    /**
     * 通过postId 获取一个文章，并保存到缓存里面，缓存一个小时，因为都为静态数据，动态数据已经分离.
     */
    public function getPostById(int $id): ?Posts
    {
        $cacheKey = $this->genPostCacheKeyById($id);

        return Cache::remember($cacheKey, 3600, function () use ($id) {
            $data = Posts::query()->with('tags')->find($id);
            if ($data instanceof Posts) {
                return $data;
            }

            return null;
        });
    }

    public function getPostsByIds(array $ids): Collection
    {
        $collection = Posts::query()->with('tags')->whereIn('id', $ids)->get();
        $collection->each(function (Posts $post) {
            if ($post->privacy == Posts::PRIVACY_PUBLIC) {
                Cache::add($this->genPostCacheKeyById($post->id), $post, 3600);
            }
        });

        return $collection;
    }

    /**
     * @param array $data
     *                    eg: ['title'=>require, content=> require, 'seo_words' => require, 'status' => require, 'privacy' => require,
     *                    'description' => ?, 'post_index' => ?],
     *
     * @throws
     */
    public function create(int $user_id, array $data, array $tagIds = []): ?Posts
    {
        DB::beginTransaction();
        try {
            $post = new Posts();
            $post->fill($data);
            $post->user_id = $user_id;
            $post->save();

            if (!empty($tagIds)) {
                $bulkInsetValues = [];
                $now = date('Y-m-d H:i:s');
                foreach ($tagIds as $tagId) {
                    $bulkInsetValues[] = ['post_id' => $post->id, 'tag_id' => $tagId, 'created_at' => $now];
                }
                //PostTagMap::query()->insert($bulkInsetValues);
                DB::table('t_post_tag_map')->insert($bulkInsetValues);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        //创建一篇文章后将缓存预热一次
        return $this->getPostById($post->id);
    }

    /**
     * @throws \Exception
     */
    public function delete(int $id, int $userId): bool
    {
        //$post = $this->getPostById($id);
        $post = Posts::query()
            ->where('id', '=', $id)
            ->first();
        if (empty($post)) {
            throw new \Exception('Not Found.', 404);
        }

        if ($post instanceof Posts && $post->user_id != $userId) {
            throw new \Exception('FORBIDDEN.', 403);
        }

        $post->delete();

        return true;
    }

    /**
     * 更新一个文章.
     *
     * @param array $data   eg: ['title' => xxx, 'content' => xxx, 'privacy' => 1]
     * @param array $tagIds eg: [1,2,3]
     *
     * @throws \Exception
     */
    public function update(int $id, int $userId, array $data, array $tagIds): bool
    {
        /** @var Posts $post */
        $post = Posts::query()
            ->where('id', '=', $id)
            ->first();

        if (empty($post)) {
            throw new \Exception('Not Found.', 404);
        }

        if ($post instanceof Posts && $post->user_id != $userId) {
            throw new \Exception('FORBIDDEN.', 403);
        }

        DB::beginTransaction();

        try {
            //将文章写入历史记录保存
            $postHistory = new PostHistory();
            $postHistory->post_id = $post->id;
            $postHistory->title = $post->title;
            $postHistory->content = $post->content;
            $postHistory->save();

            //修改文章
            foreach ($data as $key => $datum) {
                $post->$key = $datum;
            }
            $post->save();

            //处理文章tag
            $postTags = PostTagMap::query()
                ->where('post_id', '=', $post->id)
                ->get();
            $noEditTagIds = [];
            //删除了的id直接删除当前标签
            $postTags->each(function (PostTagMap $postTagMap) use (&$noEditTagIds, $tagIds) {
                if (!in_array($postTagMap->tag_id, $tagIds)) {
                    $postTagMap->delete();
                } else {
                    $noEditTagIds[] = $postTagMap->tag_id;
                }
            });
            //处理添加的tag ,对比原来的tag id，如果不在原来的tagId的数组，则是新增的
            $addTagIds = [];
            $now = date('Y-m-d H:i:s');
            foreach ($tagIds as $tagId) {
                if (!in_array($tagId, $noEditTagIds)) {
                    $addTagIds[] = [
                        'post_id' => $post->id,
                        'tag_id' => $tagId,
                        'created_at' => $now,
                    ];
                }
            }
            if (!empty($addTagIds)) {
                DB::table('t_post_tag_map')->insert($addTagIds);
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param string $targetType eg: tag, user
     * @param array  $options    ['limit' => int, 'page' => ?, 'user_id=> ?, 'next_id' => ?]
     *
     * @return array ['data' => ['Posts', 'Posts'], 'next' => bool]
     *
     * @throws
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
                    $postIds = PostTagMap::query()
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
                    /** @var Posts $post */
                    foreach ($posts as $post) {
                        if ($post->user_id == $userId && ($post->privacy == Posts::PRIVACY_HIDDEN || $post->status == Posts::STATUS_DRAFT)) {
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
                $posts = Posts::query()
                    ->with('tags')
                    ->where(['user_id' => $targetId])
                    ->when($targetId != $userId, function (Builder $query) {
                        $query->where(['privacy' => Posts::PRIVACY_PUBLIC, 'status' => Posts::STATUS_PUBLISH]);
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
     * 情报缓存的key.
     */
    public function genPostCacheKeyById(int $id): string
    {
        return "post-id:{$id}";
    }
}
