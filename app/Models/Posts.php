<?php


namespace App\Models;


use App\Events\PostDeleted;
use App\Events\PostUpdated;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property int id
 * @property int user_id
 * @property string title 标题
 * @property string description 描述
 * @property string seo_words 用作于seo的词
 * @property string post_index 文章目录
 * @property string content 内容
 * @property int status 发布状态，1为发布，2为草稿
 * @property int privacy 权限，1为公开，2为仅自己可见
 *
 * @property int commented_count 评论数
 * @property int liked_count 点赞数
 * @property int bookmarked_count 收藏数
 * @property int viewed_count 阅读数
 *
 * @property Collection|array tags
 *
 * Class Posts
 * @package App\Models
 */
class Posts extends Model
{
    use SoftDeletes;

    const STATUS_PUBLISH = 1; //发布状态
    const STATUS_DRAFT = 2; //草稿状态

    const PRIVACY_PUBLIC = 1; //公开
    const PRIVACY_HIDDEN = 2; //仅自己可见

    protected $table = "t_posts";

    //protected $appends = ['tags'];

    protected $fillable = ['user_id', 'title', 'description', 'seo_words', 'post_index', 'content', 'status', 'privacy'];

    protected $visible = ['id', 'user_id', 'title', 'description', 'seo_words', 'post_index', 'content', 'status', 'commented_count',
                          'liked_count', 'bookmarked_count', 'viewed_count', 'updated_at', 'tags', 'user'];

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
        return $this->belongsToMany(Tags::class, 't_post_tag_map', 'post_id', 'tag_id');
    }

    public function getPostIndexAttribute()
    {
        if (empty($this->attributes['post_index'])) {
            return null;
        }
        return json_decode($this->attributes['post_index'], true);
    }
}
