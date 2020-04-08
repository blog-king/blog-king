<?php

namespace App\Repository\Interfaces;

use App\Models\Post;
use App\User;
use Illuminate\Database\Eloquent\Collection;

interface PostInterface
{
    /**
     * 根据id获取一个文章.
     *
     * @param int $id
     *
     * @return Post|null
     */
    public function getPostById(int $id): ?Post;

    /**
     * 批量获取文章.
     *
     * @param array $ids
     * @param bool  $filterUnvisible 是否只显示
     *
     * @return Collection
     */
    public function getPostsByIds(array $ids, bool $filterUnvisible = true): Collection;

    /**
     * 创建文章.
     *
     * @param int   $userId
     * @param array $data
     * @param array $tagIds
     *
     * @return Post
     */
    public function create(int $userId, array $data, array $tagIds): ?Post;

    /**
     * 删除一篇文章.
     *
     * @param int  $id
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user, int $id): bool;

    /**
     * 更新一个文章.
     *
     * @param int   $id
     * @param User  $user
     * @param array $data   eg: ['title' => xxx, 'content' => xxx, 'privacy' => 1]
     * @param array $tagIds eg: [1,2,3]
     *
     * @return bool
     */
    public function update(User $user, int $id, array $data, array $tagIds): bool;
}
