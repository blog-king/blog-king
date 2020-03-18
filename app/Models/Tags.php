<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property int level 层级 eg:第一层，互联网技术--->服务端--->php，摄影技术--->相机--->单反  这样走下来，默认最高三层
 * @property int parent_id 父类id
 *
 * @property array children tag的子类
 *
 * Class Tags
 */
class Tags extends Model
{
    protected $visible = ['id', 'name', 'children'];

    protected $table = 't_tags';
}
