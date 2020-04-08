<?php

namespace App;

use App\Models\Post;
use App\Models\PostTag;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\User.
 *
 * @property int                                                                                                       $id
 * @property string                                                                                                    $name
 * @property string                                                                                                    $nickname
 * @property string|null                                                                                               $email               邮箱
 * @property string|null                                                                                               $email_verified_at
 * @property string                                                                                                    $password
 * @property string                                                                                                    $password_salt       密码加密的salt
 * @property string|null                                                                                               $remember_token
 * @property string|null                                                                                               $phone               电话
 * @property int                                                                                                       $gender              0为女， 1为男，2未设定
 * @property int                                                                                                       $login_type          0为不使用第三方账号登录，1为github登录
 * @property mixed|string                                                                                              $avatar              头像
 * @property string|null                                                                                               $title               标题
 * @property string|null                                                                                               $introduction        个人简介
 * @property array|null                                                                                                $carousel            轮播图+跳转地址
 * @property \Illuminate\Support\Carbon|null                                                                           $created_at
 * @property \Illuminate\Support\Carbon|null                                                                           $updated_at
 * @property \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property int|null                                                                                                  $notifications_count
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\PostTag[]                                            $postTags
 * @property int|null                                                                                                  $post_tags_count
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\PostTag
     */
    public function postTags()
    {
        return $this->hasMany(PostTag::class);
    }
}
