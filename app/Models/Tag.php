<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Tags.
 *
 * @property int                             $id
 * @property string                          $name
 * @property int                             $level      层级关系，第几层
 * @property int                             $parent_id  tag的父类id,默认第一层为0
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Tag query()
 * @mixin \Eloquent
 */
class Tag extends Model
{
    protected $visible = ['id', 'name', 'children'];
}
