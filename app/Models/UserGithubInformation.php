<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户github登录的资料表.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property int                             $github_id
 * @property string                          $name
 * @property string                          $nickname
 * @property string|null                     $email
 * @property string|null                     $location
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserGithubInformation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserGithubInformation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserGithubInformation query()
 * @mixin \Eloquent
 */
class UserGithubInformation extends Model
{
    protected $table = 'user_github_information';
}
