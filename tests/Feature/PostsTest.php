<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostTag;
use App\Models\Tag;
use App\Repository\Repositories\PostRepository;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PostsTest extends TestCase
{
    use DatabaseMigrations;

    private Tag $parentTag;

    private User $postOwnerUser; //文章创建者
    private User $guestUser; //游客一号
    private User $guestUser2; //游客二号
    private User $guestUser3; //游客三号

    private PostRepository $postRepository;

    private Post $post;

    private $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postRepository = $this->app->make(PostRepository::class);
        $this->faker = $this->app->make(Faker::class);

        /** @var Tag $grantParentTag */
        $grantParentTag = factory(Tag::class, 1)->create()->first;
        $parentTag = factory(Tag::class, 3)->create([
            'parent_id' => $grantParentTag->id,
            'level' => $grantParentTag->level + 1,
        ]);
        $this->parentTag = $parentTag->first();

        $users = factory(User::class, 4)->create();
        $this->postOwnerUser = $users[0];
        $this->guestUser = $users[1];
        $this->guestUser2 = $users[2];
        $this->guestUser3 = $users[3];

        //模拟数据
        $posts = factory(Post::class, 1)->create([
            'privacy' => Post::PRIVACY_PUBLIC,
            'status' => Post::STATUS_PUBLISH,
            'user_id' => $this->postOwnerUser->id,
        ])->each(function (Post $post) {
            $tags = factory(Tag::class, mt_rand(1, 3))->create([
                'parent_id' => $this->parentTag->id,
                'level' => $this->parentTag->level + 1,
            ]);
            $tags->each(function (Tag $tags) use ($post) {
                $postTagMap = new PostTag();
                $postTagMap->post_id = $post->id;
                $postTagMap->user_id = $post->user_id;
                $postTagMap->tag_id = $tags->id;
                $postTagMap->name = $tags->name;
                $postTagMap->save();
            });
        });

        /** @var Post $post */
        $post = $posts->first();
        $this->post = $post;
    }

    /**
     * 已经发布了的文章.
     */
    public function testApiForShow()
    {
        //开始测试
        $testPost = $this->postRepository->getPostById($this->post->id);
        $this->assertSame($testPost->user_id, $this->post->user_id);

        //不是自己的文章不允许返回json
        $response = $this->actingAs($this->guestUser)->get(route('post-api-show',
            ['id' => $this->post->id]))->header('accept', 'application/json');
        $this->assertTrue(403 == $response->getStatusCode());

        $response = $this->actingAs($this->postOwnerUser)->get(route('post-api-show', ['id' => $this->post->id]),
            ['accept' => 'application/json']);
        $response->assertOk();

        $result = json_decode($response->content());
        $this->assertNotEmpty($result->data->title);
        $this->assertNotEmpty($result->data->content);
        $this->assertNotEmpty($result->data->seo_words);
    }


    public function testApiForUpdate()
    {
        $tags = factory(Tag::class, 3)->create([
            'parent_id' => $this->parentTag->id, 'level' => $this->parentTag->level + 1,
        ]);
        $tagIds = [];
        $tags->each(function (Tag $tag) use (&$tagIds) {
            $tagIds[] = $tag->id;
        });

        $data = $this->genUpdatePostData();
        $data = array_merge($data, ['tag_ids' => implode(',', $tagIds)]);

        // not your posts
        $response = $this->actingAs($this->guestUser)
            ->patch(route('post-api-update', ['id' => $this->post->id]), $data);
        $response->assertForbidden();

        //将权限修改为隐藏
        $response = $this->actingAs($this->postOwnerUser)
            ->patch(route('post-api-update', ['id' => $this->post->id]),
                array_merge(['privacy' => Post::PRIVACY_HIDDEN], $data));
        $response->assertForbidden();

        //超频请求
        $response = $this->actingAs($this->postOwnerUser)
            ->patch(route('post-api-update', ['id' => $this->post->id]), $data);
        $response->assertStatus(429);

        $rateLimit = $this->app->make(RateLimiter::class);
        $postRateLimitKey = "post-update-rate-limit-{$this->postOwnerUser->id}";
        $rateLimit->resetAttempts($postRateLimitKey);

        //ok
        $response = $this->actingAs($this->postOwnerUser)->patch(route('post-api-update', ['id' => $this->post->id]),
            $data);
        $response->assertOk();
        $result = json_decode($response->content(), true);
        $this->assertTrue($result['data']['success']);
    }

    public function testApiForDelete()
    {
        $response = $this->actingAs($this->guestUser)->delete(route('post-api-delete', ['id' => $this->post->id]));
        $response->assertForbidden();

        $response = $this->actingAs($this->postOwnerUser)->delete(route('post-api-delete', ['id' => $this->post->id]));
        $response->assertOk();

        $result = json_decode($response->content(), true);
        $this->assertTrue($result['data']['success']);
    }

    /**
     * 生成修改文章的内容.
     *
     * @return array
     */
    private function genUpdatePostData()
    {
        $title = $this->faker->sentences[0];
        $content = $this->faker->randomHtml();

        return [
            'content' => $content,
            'title' => $title,
        ];
    }

    public function testApiForCreate()
    {
        $tags = factory(Tag::class, 3)->create([
            'parent_id' => $this->parentTag->id, 'level' => $this->parentTag->level + 1,
        ]);
        $tagIds = [];
        $tags->each(function (Tag $tag) use (&$tagIds) {
            $tagIds[] = $tag->id;
        });
        $data = [
            'title' => $this->faker->sentences[0],
            'content' => $this->faker->randomHtml(),
            'description' => $this->faker->sentences[0],
            'seo_words' => implode(',', $this->faker->words),
            'status' => Post::STATUS_PUBLISH,
            'privacy' => Post::PRIVACY_PUBLIC,
            'tag_ids' => implode(',', $tagIds),
        ];

        $response = $this->actingAs($this->postOwnerUser)->post(route('post-api-create'), $data);
        $response->assertOk();

        $result = json_decode($response->content());
        $this->assertNotEmpty($result->data->title);
        $this->assertNotEmpty($result->data->content);
        $this->assertNotEmpty($result->data->seo_words);
        $tags->each(function (Tag $tag) use ($result) {
            $this->assertDatabaseHas('post_tags', ['post_id' => $result->data->id, 'tag_id' => $tag->id]);
        });

        //超频请求
        $response = $this->actingAs($this->postOwnerUser)->post(route('post-api-create'), $data);
        $response->assertStatus(429);
    }

    public function testApiForUserList()
    {
        factory(Post::class, 10)->create([
            'privacy' => Post::PRIVACY_PUBLIC,
            'status' => Post::STATUS_PUBLISH,
            'user_id' => $this->postOwnerUser->id,
        ]);

        $response = $this->actingAs($this->postOwnerUser)
            ->get(route('post-api-list', [
                'target_type' => PostRepository::TARGET_TYPE_USER,
                'target_id' => $this->postOwnerUser->id,
                'limit' => 1,
            ]));

        $response->assertOk();
        $result = json_decode($response->content());

        $this->assertTrue($result->data->has_next);
        $this->assertSame(count($result->data->list), 1);
        $post = (array) $result->data->list[0];
        $this->assertArrayHasKey('id', $post);
        $this->assertArrayHasKey('title', $post);
        $this->assertArrayHasKey('description', $post);
        $this->assertArrayHasKey('thumbnail', $post);
        $this->assertArrayHasKey('user_id', $post);
        $this->assertArrayHasKey('status', $post);
        $this->assertArrayHasKey('commented_count', $post);
        $this->assertArrayHasKey('liked_count', $post);
        $this->assertArrayHasKey('viewed_count', $post);
        $this->assertArrayHasKey('bookmarked_count', $post);
    }
}
