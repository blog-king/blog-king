<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property int type_id
 *
 * Class Tags
 */
class Tags extends Model
{
    protected $visible = ['id', 'name'];

    protected $table = 't_tags';
}
