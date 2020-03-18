<?php

namespace App\Repository\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface TagInterface
{
    /**
     * * 获取一大类tag.
     *
     * @param int $parent_id
     *
     * @return Collection
     */
    public function getTagsByParentId(int $parent_id): Collection;

    /**
     * 获取第一第二层分类的tag.
     *
     * @return Collection
     */
    public function getLevel0andLevel(): Collection;
}
