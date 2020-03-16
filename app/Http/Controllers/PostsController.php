<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post;
use App\Models\Posts;
use App\Repository\Repositories\PostRepository;
use App\Repository\Repositories\UserRepository;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostsController extends Controller
{
    private PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * 文章显示接口.
     *
     * @return \Illuminate\Http\JsonResponse
     * @apiGroup post
     *
     * @api {GET} /post/{id} 文章显示接口
     *
     * @apiSuccess {object[]} data 返回结果
     * @apiSuccess {int} data.id 文章的id
     * @apiSuccess {int} data.user_id 文章的userId
     * @apiSuccess {string} data.title 文章标题
     * @apiSuccess {string} data.description 文章描述
     * @apiSuccess {string} data.seo_words 文章seo的词
     * @apiSuccess {string} data.post_index 文章目录，空则没有设置目录
     * @apiSuccess {string} data.content 文章内容
     * @apiSuccess {string} data.status 文章状态 1为发布，2为草稿
     * @apiSuccess {string} data.commented_count 评论数
     * @apiSuccess {string} data.liked_count  点赞数
     * @apiSuccess {string} data.bookmarked_count 收藏数
     * @apiSuccess {string} data.viewed_count 阅读数
     *
     * @apiSuccess {object} data.user 文章的用户
     * @apiSuccess {int} data.user.id 文章的用户id
     * @apiSuccess {string} data.user.name 文章的用户名字
     * @apiSuccess {string} data.user.avatar 文章的用户头像
     */
    public function show(UserRepository $userRepository, Request $request, int $id)
    {
        $userId = Auth::id();
        $post = $this->postRepository->getPostById($id);
        if (!$post instanceof Posts || $post->privacy != Posts::PRIVACY_PUBLIC || $post->status != Posts::STATUS_PUBLISH) {
            throw new NotFoundHttpException(__('post.404'));
        }

        if ($post->user_id != $userId) {
            throw new HttpException(403, __('post.403_not_your_post'));
        }

        //将文章跟user的信息分离开，方便缓存的管理
        $postOwnerId = $post->user_id;
        $postOwner = $userRepository->getUserById($postOwnerId);
        $post->setAttribute('user', $postOwner);

        if ($request->wantsJson()) {
            return $this->buildReturnData($post, 200);
        }

        //todo return view
        //return $post;
    }

    /**
     * @apiGroup post
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @api {GET} /posts 文章列表api
     * @apiParam {int} target_id 目标类型的id,eg:用户类型,则target_id 为user_id
     * @apiParam {string} target_type 目标类型,eg:tag, user
     * @apiParam {int} limit 限制多少页
     * @apiParam {int} page 第几页，默认第一页开始
     *
     * @apiSuccess {int} list.id 文章的id
     * @apiSuccess {string} list.title 文章的title
     * @apiSuccess {description} list.description 文章的描述
     * @apiSuccess {int} list.user_id 文章的用户id
     * @apiSuccess {int} list.status 文章的类型，1为发布，2为草稿
     * @apiSuccess {string} list.commented_count 评论数
     * @apiSuccess {string} list.liked_count  点赞数
     * @apiSuccess {string} list.bookmarked_count 收藏数
     * @apiSuccess {string} list.viewed_count 阅读数
     *
     * @apiSuccess {bool} next 文章下一页, true 则有下一页，false则没有
     */
    public function postsList(Request $request)
    {
        $request->validate([
            'target_id' => 'int',
            'target_type' => 'in:'.PostRepository::TARGET_TYPE_USER.','.PostRepository::TARGET_TYPE_TAG,
            'limit' => 'int',
            'page' => 'int',
        ]);

        $targetType = $request->input('target_type');
        $targetId = $request->input('target_id');
        $limit = min(abs($request->input('limit', 10)), 100);
        $page = abs($request->input('page', 1));

        $userId = Auth::id();

        $options = [
            'limit' => $limit,
            'page' => $page,
            'user_id' => $userId,
        ];

        $data = $this->postRepository->getPosts($targetType, $targetId, $options);

        $result = [];
        $resultPostKeys = ['id', 'title', 'description', 'updated_at', 'user_id', 'status', 'commented_count', 'liked_count', 'bookmarked_count', 'viewed_count'];
        foreach ($data['data'] as $datum) {
            $tmp = [];
            foreach ($resultPostKeys as $resultPostKey) {
                $tmp[$resultPostKey] = $datum->$resultPostKey;
            }
            $result[] = $tmp;
        }

        return $this->buildReturnData(['list' => $result, 'next' => $data['next']]);
    }

    /**
     * 创建文章接口.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @apiGroup post
     *
     * @api {POST} /post 创建文章接口
     * @apiParam {string} title 文章标题
     * @apiParam {string} description  文章描述，如果不填则会使用文章内容去除html标签的前100个字符
     * @apiParam {string} seo_words 用于seo的词，可让用户自定义
     * @apiParam {string} post_index 文章的目录
     * @apiParam {string} content 文章内容
     * @apiParam {string} tag_ids tag的id，用英文逗号分开
     * @apiParam {int} status 文章状态，默认为发布状态
     * @apiParam {int} privacy 文章权限，默认为所有人可读状态
     *
     * @apiSuccess {int} code 自定义的code返回
     * @apiSuccess {string} message 信息返回
     * @apiSuccess {object[]} data 返回结果
     * @apiSuccess {int} data.id 文章的id
     * @apiSuccess {int} data.user_id 文章的userId
     * @apiSuccess {string} data.title
     * @apiSuccess {string} data.description
     * @apiSuccess {string} data.seo_words
     * @apiSuccess {string} data.post_index
     * @apiSuccess {string} data.content
     * @apiSuccess {string} data.status
     * @apiSuccess {object[]} data.tags 文章的tag
     * @apiSuccess {int} data.tags.id tag的id
     * @apiSuccess {string} data.tags.name
     */
    public function create(RateLimiter $rateLimiter, Post $request)
    {
        $userId = Auth::id();

        $postRateLimitKey = "post-create-rate-limit-{$userId}";

        //频率限制，60秒一个用户只能发一篇文章
        $rateLimiter->hit($postRateLimitKey);
        if ($rateLimiter->tooManyAttempts($postRateLimitKey, 2)) {
            $result = $this->buildReturnData(null, 429);
        } else {
            $title = $request->input('title');
            $description = $request->input('description');
            $seoWords = $request->input('seo_words');
            $postIndex = $request->input('post_index');
            $status = $request->input('status');
            $privacy = $request->input('privacy');

            //todo 检查是否包含当前的tag，是否捏造tagId
            $tagIds = array_filter(explode(',', $request->input('tag_ids')));

            //todo 需要过滤content 防止注入
            $content = $request->input('content');

            if (empty($description)) {
                $description = substr(strip_tags($content), 0, 100);
            }

            $data = [
                'title' => $title,
                'description' => $description,
                'content' => $content,
                'seo_words' => $seoWords,
                'post_index' => $postIndex,
                'status' => $status,
                'privacy' => $privacy,
            ];
            try {
                $post = $this->postRepository->create($userId, $data, $tagIds);
                $result = $this->buildReturnData($post);
            } catch (\Exception $e) {
                Log::warning('create post error '.$e->getMessage());
                $result = $this->buildReturn500(0, __('post.create_error'));
            }
        }

        return $result;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws
     *
     * @apiGroup post
     *
     * @api {PATCH} /post/{$id} 文章更新
     * @apiSuccess {bool} success 是否成功
     */
    public function update(RateLimiter $rateLimiter, Request $request, int $id)
    {
        $userId = Auth::id();

        $postRateLimitKey = "post-update-rate-limit-{$userId}";
        $rateLimiter->hit($postRateLimitKey);

        if ($rateLimiter->tooManyAttempts($postRateLimitKey, 2)) {
            return $this->buildReturnData(null, 429);
        }

        $privacy = $request->input('privacy');
        //只能由隐藏改成发布
        if ($privacy && $privacy != Posts::PRIVACY_PUBLIC) {
            throw new HttpException(403, __('post.403_can_not_update_post_privacy'));
        }

        $title = $request->input('title');
        //todo 检查是否包含当前的tag，是否捏造tagId
        $tagIds = (array) array_filter(explode(',', $request->input('tag_ids')));

        //todo 需要过滤content 防止注入
        $content = $request->input('content');

        $data['content'] = $content;
        $title && $data['title'] = $title;
        try {
            $message = null;
            $result = ['success' => $this->postRepository->update($id, $userId, $data, $tagIds)];

            return $this->buildReturnData($result);
        } catch (\Exception $e) {
            $code = $e->getCode();
            if ($code === 404) {
                $message = __('post.404');
            } elseif ($code == 403) {
                $message = __('post.403_not_your_post');
            } else {
                Log::warning('update post error '.$e->getMessage());
                $message = __('post.500_update_post');
            }
            throw new HttpException($code, $message);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws
     *
     * @apiGroup post
     *
     * @api {DELETE} /post/{$id} 文章删除
     * @apiSuccess {bool} success 是否成功
     */
    public function delete(int $id)
    {
        try {
            $userId = Auth::id();
            $result = ['success' => $this->postRepository->delete($id, $userId)];

            return $this->buildReturnData($result);
        } catch (\Exception $e) {
            $data = ['success' => false];
            $code = $e->getCode();

            if ($code === 404) {
                $message = __('post.404');
            } elseif ($code === 403) {
                $message = __('post.403_not_your_post');
            } else {
                Log::warning('delete post error '.$e->getMessage());
                $code = 500;
                $message = __('post.500_delete_post');
            }
            throw new HttpException($code, $message);
        }
    }
}
