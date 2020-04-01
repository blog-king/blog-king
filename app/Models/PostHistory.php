<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 文章发布历史.
 *
 * @property int                        $id
 * @property int                        $post_id    文章id
 * @property string                     $title
 * @property string                     $content
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PostHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PostHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PostHistory query()
 * @mixin \Eloquent
 */
class PostHistory extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['post_id', 'title', 'content'];
}
