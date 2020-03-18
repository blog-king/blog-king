<?php

namespace App\Repository\Repositories;

use App\Models\Tags;
use App\Repository\Interfaces\TagInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TagRepository implements TagInterface
{
    /**
     * 根据tag的parentId Tags.
     *
     * @param int $parent_id
     *
     * @return Collection
     */
    public function getTagsByParentId(int $parent_id): Collection
    {
        $cacheKey = 'tags-parent-id:'.$parent_id;

        return Cache::remember($cacheKey, 3600, function () use ($parent_id) {
            return Tags::query()->where(['parent_id' => $parent_id])->get();
        });
    }

    /**
     * 批量获取tags.
     *
     * @param array $tagIds
     *
     * @return array
     */
    public function getTagByIds(array $tagIds): array
    {
        $data = Tags::query()->whereIn('id', $tagIds)->get();
        //将id顺序调整为传入tagId顺序
        $tmp = $result = [];
        $data->each(function (Tags $tag) use (&$tmp) {
            $tmp[$tag->id] = $tag;
        });
        foreach ($tagIds as $tagId) {
            $result[] = $tmp[$tagId];
        }

        return $result;
    }

    /**
     * 获取全部tag.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Tags::query()->get();
    }

    /**
     * 获取第一第二层分类的tag.
     *
     * @return Collection
     */
    public function getLevel0andLevel(): Collection
    {
        //默认获取两层返回，第三层由第二层id来获取，考虑到数据量不大，所以可以放松考虑
        $cacheKey = 'tags-level0-and-level1';

        return Cache::remember($cacheKey, 3600, function () {
            return Tags::query()->whereIn('level', [0, 1])->get();
        });
    }
}
