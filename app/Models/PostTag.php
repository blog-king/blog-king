<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PostTag.
 *
 * @property int                             $id
 * @property int                             $user_id    用户ID
 * @property int                             $post_id    文章 ID
 * @property int                             $tag_id     标签 ID，0表示自定义标签
 * @property string                          $name       标签
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PostTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PostTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PostTag query()
 * @mixin \Eloquent
 */
class PostTag extends Model
{
    protected $fillable = ['post_id', 'tag_id', 'user_id', 'name'];
}
