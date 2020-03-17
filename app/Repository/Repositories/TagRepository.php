<?php


namespace App\Repository\Repositories;


use App\Models\Tags;
use App\Repository\Interfaces\TagInterface;
use Illuminate\Database\Eloquent\Collection;

class TagRepository implements TagInterface
{

    /**
     * 根据tag的parentId 批量Tags
     *
     * @param int $parent_id
     * @param int $level
     * @return Collection
     */
    public function getTagsByParentId(int $parent_id, int $level): Collection
    {
        return Tags::query()->where(['parent_id' => $parent_id, 'level' => $level])->get();
    }

}
