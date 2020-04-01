<?php

namespace App;

use App\Models\Post;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\User.
 *
 * @property mixed|string                                                                                              $avatar
 * @property mixed                                                                                                     $introduction
 * @property mixed                                                                                                     $title
 * @property \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property int|null                                                                                                  $notifications_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Post[]                                               $posts
 * @property int|null                                                                                                  $posts_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use Notifiable;

    const DEFAULT_AVATAR = '';

    //登录方式为github登录
    const LOGIN_TYPE_GITHUB = 1;

    protected $table = 't_users';

    protected $hidden = ['password'];

    protected $visible = ['id', 'name', 'nickname', 'phone', 'sex', 'avatar', 'introduction', 'carousel'];

    protected $casts = [
        'carousel' => 'array',
    ];

    /**
     * 获取用户头像，如果用户头像没有设置则返回默认头像.
     *
     * @return mixed|string
     */
    public function getAvatarAttribute()
    {
        if (empty($this->attributes['avatar'])) {
            //return self::DEFAULT_AVATAR;
            //todo 临时使用外部头像地址
            return 'https://api.adorable.io/avatars/108/'.md5($this->attributes['name']).'.png';
        }

        return $this->attributes['avatar'];
    }

    public function getIntroductionAttribute()
    {
        return $this->attributes['introduction'] ?? '这个家伙很懒~';
    }

    public function getTitleAttribute()
    {
        return $this->attributes['title'] ?? $this->attributes['name'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Post
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
