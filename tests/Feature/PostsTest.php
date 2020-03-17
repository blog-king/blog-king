<?php

namespace Tests\Feature;

use App\Models\Posts;
use App\Models\PostTagMap;
use App\Models\Tags;
use App\Repository\Repositories\PostRepository;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PostsTest extends TestCase
{
    use DatabaseMigrations;

    private Tags $parentTag;

    private User $postOwnerUser; //文章创建者
    private User $guestUser; //游客一号
    private User $guestUser2; //游客二号
    private User $guestUser3; //游客三号

    private PostRepository $postRepository;

    private Posts $post;

    private $faker;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var Tags $grantParantTag */
        $grantParantTag = factory(Tags::class, 1)->create()->first;
        $parentTag = factory(Tags::class, 3)->create([
            'parent_id' => $grantParantTag->id,
            'level' => $grantParantTag->level + 1,
        ]);
        $this->parentTag = $parentTag->first();

        $users = factory(User::class, 4)->create();
        $this->postOwnerUser = $users[0];
        $this->guestUser = $users[1];
        $this->guestUser2 = $users[2];
        $this->guestUser3 = $users[3];

        //模拟数据
        $posts = factory(Posts::class, 1)->create([
            'privacy' => Posts::PRIVACY_PUBLIC,
            'status' => Posts::STATUS_PUBLISH,
            'user_id' => $this->postOwnerUser->id,
        ])->each(function (Posts $posts) {
            $tags = factory(Tags::class, mt_rand(1, 3))->create([
                'parent_id' => $this->parentTag->id,
                'level' => $this->parentTag->level + 1,
            ]);
            $tags->each(function (Tags $tags) use ($posts) {
                $postTagMap = new PostTagMap();
                $postTagMap->post_id = $posts->id;
                $postTagMap->tag_id = $tags->id;
                $postTagMap->save();
            });
        });

        /** @var Posts $post */
        $post = $posts->first();
        $this->post = $post;

        $this->postRepository = $this->app->make(PostRepository::class);
        $this->faker = $this->app->make(Faker::class);
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
        $response = $this->actingAs($this->guestUser)->get(route('post-api-show', ['id' => $this->post->id]))->header('accept', 'application/json');
        $this->assertTrue($response->getStatusCode() == 403);

        $response = $this->actingAs($this->postOwnerUser)->get(route('post-api-show', ['id' => $this->post->id]), ['accept' => 'application/json']);
        $response->assertOk();

        $result = json_decode($response->content());
        $this->assertNotEmpty($result->data->title);
        $this->assertNotEmpty($result->data->content);
        $this->assertNotEmpty($result->data->seo_words);
    }

    /**
     * @throws \Exception
     */
    public function testPostRepositoryForUpdate()
    {
        $tags = factory(Tags::class, 3)->create(['parent_id' => $this->parentTag->id, 'level' => $this->parentTag->level + 1]);
        $tagIds = [];
        $tags->each(function (Tags $tag) use (&$tagIds) {
            $tagIds[] = $tag->id;
        });

        $oldTagIds = $this->post->tags->pluck('id')->all();
        $data = $this->genUpdatePostData();
        //完全修改tagIds
        $this->assertTrue($this->postRepository->update($this->post->id, $this->postOwnerUser->id, $data, $tagIds));
        $updatedPost = $this->postRepository->getPostById($this->post->id);
        $this->assertSame($updatedPost->title, $data['title']);
        $this->assertSame($updatedPost->content, $data['content']);
        $this->assertSame($tagIds, $updatedPost->tags->pluck('id')->all());
        $this->assertDatabaseHas('t_post_history', ['post_id' => $this->post->id, 'title' => $this->post->title, 'content' => $this->post->content]);

        //不修改tagIds
        $this->assertTrue($this->postRepository->update($this->post->id, $this->postOwnerUser->id, $this->genUpdatePostData(), $oldTagIds));
        $updatedPost = $this->postRepository->getPostById($this->post->id);
        $this->assertSame($oldTagIds, $updatedPost->tags->pluck('id')->all());

        //修改部分tagIds
        $updatedTagIds = [$oldTagIds[0], $tagIds[0]];
        $this->assertTrue($this->postRepository->update($this->post->id, $this->postOwnerUser->id, $this->genUpdatePostData(), $updatedTagIds));
        $updatedPost = $this->postRepository->getPostById($this->post->id);
        $this->assertSame($updatedTagIds, $updatedPost->tags->pluck('id')->all());

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(403);
        $this->postRepository->update($this->post->id, $this->guestUser->id, $data, $tagIds);
    }

    public function testApiForUpdate()
    {
        $tags = factory(Tags::class, 3)->create(['parent_id' => $this->parentTag->id, 'level' => $this->parentTag->level + 1]);
        $tagIds = [];
        $tags->each(function (Tags $tag) use (&$tagIds) {
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
            ->patch(route('post-api-update', ['id' => $this->post->id]), array_merge(['privacy' => Posts::PRIVACY_HIDDEN], $data));
        $response->assertForbidden();

        //超频请求
        $response = $this->actingAs($this->postOwnerUser)
            ->patch(route('post-api-update', ['id' => $this->post->id]), $data);
        $response->assertStatus(429);

        $rateLimit = $this->app->make(RateLimiter::class);
        $postRateLimitKey = "post-update-rate-limit-{$this->postOwnerUser->id}";
        $rateLimit->resetAttempts($postRateLimitKey);

        //ok
        $response = $this->actingAs($this->postOwnerUser)
            ->patch(route('post-api-update', ['id' => $this->post->id]), $data);
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
        $tags = factory(Tags::class, 3)->create(['parent_id' => $this->parentTag->id, 'level' => $this->parentTag->level + 1]);
        $tagIds = [];
        $tags->each(function (Tags $tag) use (&$tagIds) {
            $tagIds[] = $tag->id;
        });
        $data = [
            'title' => $this->faker->sentences[0],
            'content' => $this->faker->randomHtml(),
            'description' => $this->faker->sentences[0],
            'seo_words' => implode(',', $this->faker->words),
            'status' => Posts::STATUS_PUBLISH,
            'privacy' => Posts::PRIVACY_PUBLIC,
            'tag_ids' => implode(',', $tagIds),
        ];

        $response = $this->actingAs($this->postOwnerUser)->post(route('post-api-create'), $data);
        $response->assertOk();

        $result = json_decode($response->content());
        $this->assertNotEmpty($result->data->title);
        $this->assertNotEmpty($result->data->content);
        $this->assertNotEmpty($result->data->seo_words);
        $tags->each(function (Tags $tag) use ($result) {
            $this->assertDatabaseHas('t_post_tag_map', ['post_id' => $result->data->id, 'tag_id' => $tag->id]);
        });

        //超频请求
        $response = $this->actingAs($this->postOwnerUser)->post(route('post-api-create'), $data);
        $response->assertStatus(429);
    }

    public function testRepositoryForUserList()
    {
        factory(Posts::class, 10)->create([
            'privacy' => Posts::PRIVACY_PUBLIC,
            'status' => Posts::STATUS_PUBLISH,
            'user_id' => $this->postOwnerUser->id,
        ]);

        $result = $this->postRepository->getPosts(PostRepository::TARGET_TYPE_USER, $this->postOwnerUser->id, ['limit' => 5, 'page' => 1, 'user_id' => $this->postOwnerUser->id]);
        $this->assertSame(count($result['data']), 5);
        $this->assertTrue($result['next']);

        $result = $this->postRepository->getPosts(PostRepository::TARGET_TYPE_USER, $this->postOwnerUser->id, ['limit' => 50, 'page' => 1, 'user_id' => $this->postOwnerUser->id]);
        $this->assertFalse($result['next']);

        //----------------------------------------------------------------------------------------///
        //测试隐私文章
        factory(Posts::class, 10)->create([
            'privacy' => Posts::PRIVACY_HIDDEN,
            'status' => Posts::STATUS_PUBLISH,
            'user_id' => $this->guestUser->id,
        ]);

        //隐私文章不是自己无法获取到
        $result = $this->postRepository->getPosts(PostRepository::TARGET_TYPE_USER, $this->guestUser2->id, ['limit' => 50, 'page' => 1, 'user_id' => $this->guestUser2->id]);
        $this->assertEmpty($result['data']);
        $this->assertFalse($result['next']);

        //自己能获取到自己的隐私文章，并测试分页零界点
        $result = $this->postRepository->getPosts(PostRepository::TARGET_TYPE_USER, $this->guestUser->id, ['limit' => 10, 'page' => 1, 'user_id' => $this->guestUser->id]);
        $this->assertSame(count($result['data']), 10);
        $this->assertFalse($result['next']);

        //----------------------------------------------------------------------------------------///
        //测试草稿文章
        factory(Posts::class, 10)->create([
            'privacy' => Posts::PRIVACY_PUBLIC,
            'status' => Posts::STATUS_DRAFT,
            'user_id' => $this->guestUser2->id,
        ]);
        //草稿文章不是自己无法获取到
        $result = $this->postRepository->getPosts(PostRepository::TARGET_TYPE_USER, $this->guestUser3->id, ['limit' => 50, 'page' => 1, 'user_id' => $this->guestUser3->id]);
        $this->assertEmpty($result['data']);
        $this->assertFalse($result['next']);

        //自己能获取自己的草稿文章
        $result = $this->postRepository->getPosts(PostRepository::TARGET_TYPE_USER, $this->guestUser2->id, ['limit' => 10, 'page' => 1, 'user_id' => $this->guestUser2->id]);
        $this->assertSame(count($result['data']), 10);
    }

    public function testApiForUserList()
    {
        factory(Posts::class, 10)->create([
            'privacy' => Posts::PRIVACY_PUBLIC,
            'status' => Posts::STATUS_PUBLISH,
            'user_id' => $this->postOwnerUser->id,
        ]);

        $response = $this->actingAs($this->postOwnerUser)
            ->get(route('post-api-list', ['target_type' => PostRepository::TARGET_TYPE_USER, 'target_id' => $this->postOwnerUser->id, 'limit' => 5]));
        $response->assertOk();
        $result = json_decode($response->content());

        $this->assertTrue($result->data->next);
        $this->assertSame(count($result->data->list), 5);
        $post = (array)$result->data->list[0];
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
