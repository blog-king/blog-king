<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property int level 层级
 * @property int parent_id 父类id
 *
 * Class Tags
 */
class Tags extends Model
{
    protected $visible = ['id', 'name'];

    protected $table = 't_tags';
}
