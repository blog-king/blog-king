<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


/**
 * 用户github登录的资料表
 * @property int id
 * @property int github_id
 * @property int user_id
 * @property string name github的name,唯一索引
 * @property string nickname github的昵称
 * @property string email
 * @property string location 地理位置
 *
 * Class UserGithubInformation
 * @package App\Models
 */
class UserGithubInformation extends Model
{
    protected $table = "t_user_github_information";


}
