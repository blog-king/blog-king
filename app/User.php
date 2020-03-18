<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int id
 * @property string name
 * @property string nickname
 * @property array carousel //轮播图，json，图片地址 + 跳转地址 cover[可为空，默认用颜色处理]、title、description、action
 * @property string password
 * @property string password_salt
 * @property string email
 * @property string phone
 * @property int gender 0为女， 1为男，2未设定
 * @property int login_type 0为不使用第三方账号登录，1为github登录
 * @property string avatar 头像
 * @property string introduction 个人简介
 * Class User
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
     * @return mixed|string
     */
    public function getAvatarAttribute()
    {
        if (empty($this->attributes['avatar'])) {
            //return self::DEFAULT_AVATAR;
            //todo 临时使用外部头像地址
            return 'https://api.adorable.io/avatars/60/'.md5($this->attributes['name']).'.png';
        }

        return $this->attributes['avatar'];
    }

    public function getIntroductionAttribute()
    {
        return $this->attributes['introduction'] ?? '这个家伙很懒~';
    }
}
