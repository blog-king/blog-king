<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 文章发布历史.
 *
 * @property int id
 * @property int post_id
 * @property string title
 * @property string content
 *
 * Class PostHistory
 */
class PostHistory extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['post_id', 'title', 'content'];

    protected $table = 't_post_history';
}
