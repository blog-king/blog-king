<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int post_id
 * @property int tag_id
 *
 * Class PostTagMap
 */
class PostTagMap extends Model
{
    protected $fillable = ['post_id', 'tag_id'];

    protected $table = 't_post_tag_map';
}
