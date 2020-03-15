<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property int level
 * Class TagType
 * @package App\Models
 */
class TagType extends Model
{
    const UPDATED_AT = null;

    protected $table = 't_tag_type';
}
