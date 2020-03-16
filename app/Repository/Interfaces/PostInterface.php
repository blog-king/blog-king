<?php


namespace App\Repository\Interfaces;


use App\Models\Posts;
use Illuminate\Database\Eloquent\Collection;

interface PostInterface
{
    /**
     * 根据id获取一个文章
     * @param int $id
     * @return Posts|null
     */
    public function getPostById(int $id): ?Posts;

    /**
     * 批量获取文章
     * @param array $id
     * @return Collection
     */
    public function getPostsByIds(array $id): Collection;

    /**
     * 创建文章
     * @param int $userId
     * @param array $data
     * @param array $tagIds
     * @return Posts
     */
    public function create(int $userId, array $data, array $tagIds): ?Posts;

    /**
     * 删除一篇文章
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function delete(int $id, int $userId): bool;

    /**
     * 更新一个文章
     * @param int $id
     * @param int $userId
     * @param array $data eg: ['title' => xxx, 'content' => xxx, 'privacy' => 1]
     * @param array $tagIds eg: [1,2,3]
     * @return bool
     */
    public function update(int $id, int $userId, array $data, array $tagIds): bool;

    /**
     * 获取文章列表，根据文章属性，如userId,tagId
     *
     * @param string $targetType
     * @param int $targetId
     * @param array $options
     * @return array
     */
    public function getPosts(string $targetType, int $targetId, array $options = []): array;
}
