<?php

namespace App\Models;

use App\Events\PostDeleted;
use App\Events\PostUpdated;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Post.
 *
 * @property int                                                        $id
 * @property int                                                        $user_id
 * @property string                                                     $title            标题
 * @property string                                                     $description      描述
 * @property string|null                                                $thumbnail        缩略图
 * @property string                                                     $seo_words        用作于seo的词
 * @property string|null                                                $post_index       文章目录
 * @property string                                                     $content          内容
 * @property int                                                        $status           发布状态，1位发布，2为草稿
 * @property int                                                        $privacy          权限，1为公开，2为仅自己可见
 * @property int                                                        $commented_count  评论数量
 * @property int                                                        $liked_count      点赞数量
 * @property int                                                        $bookmarked_count 收藏数量
 * @property int                                                        $viewed_count     收藏数量
 * @property \Illuminate\Support\Carbon|null                            $deleted_at
 * @property \Illuminate\Support\Carbon|null                            $created_at
 * @property \Illuminate\Support\Carbon|null                            $updated_at
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property int|null                                                   $tags_count
 * @property \App\User                                                  $user
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Post newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Post onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Post query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Post visible()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Post withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Post withoutTrashed()
 * @mixin \Eloquent
 */
class Post extends Model
{
    use SoftDeletes;

    const STATUS_PUBLISH = 1; //发布状态
    const STATUS_DRAFT = 2; //草稿状态

    const PRIVACY_PUBLIC = 1; //公开
    const PRIVACY_HIDDEN = 2; //仅自己可见

    //protected $appends = ['tags'];

    protected $fillable = [
        'user_id', 'title', 'description', 'seo_words', 'post_index', 'content', 'status', 'privacy', 'thumbnail',
    ];

    protected $visible = [
        'id', 'user_id', 'title', 'description', 'thumbnail', 'seo_words', 'post_index', 'content', 'status',
        'commented_count',
        'liked_count', 'bookmarked_count', 'viewed_count', 'updated_at', 'tags', 'user',
    ];

    protected $dispatchesEvents = [
        'updated' => PostUpdated::class,
        'deleted' => PostDeleted::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, PostTag::class, 'post_id', 'tag_id');
    }

    public function getPostIndexAttribute()
    {
        if (empty($this->attributes['post_index'])) {
            return null;
        }

        return json_decode($this->attributes['post_index'], true);
    }

    public function getThumbnailAttribute()
    {
        if (empty($this->attributes['thumbnail'])) {
            //todo 临时使用外部图片地址
            return 'https://api.adorable.io/avatars/150/'.md5($this->attributes['id']).'.png';
        }

        return $this->attributes['thumbnail'];
    }

    public function scopeVisible(Builder $builder)
    {
        return $builder->where([
            'status' => self::STATUS_PUBLISH,
            'privacy' => self::PRIVACY_PUBLIC,
        ]);
    }
}
