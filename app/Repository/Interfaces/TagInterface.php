<?php


namespace App\Repository\Interfaces;


use Illuminate\Database\Eloquent\Collection;

interface TagInterface
{

    /**
     * * 获取一大类tag
     * @param int $parent_id
     * @param int $level
     * @return Collection
     */
    public function getTagsByParentId(int $parent_id, int $level) : Collection;
}
