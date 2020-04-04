<?php

namespace Tests\Feature\tests\Unit;

use App\Models\Post;
use App\Models\PostTag;
use App\Models\Tag;
use App\Repository\Repositories\PostRepository;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostsRepositoryTest extends TestCase
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
     * @throws \Exception
     */
    public function testUpdate()
    {
        $tags = factory(Tag::class, 3)->create([
            'parent_id' => $this->parentTag->id, 'level' => $this->parentTag->level + 1,
        ]);
        $tagIds = $tags->pluck('id')->all();

        $oldTagIds = $this->post->tags->pluck('id')->all();
        $data = $this->genUpdatePostData();
        //完全修改tagIds
        $this->assertTrue($this->postRepository->update($this->postOwnerUser, $this->post->id, $data, $tagIds));
        $updatedPost = $this->postRepository->getPostById($this->post->id);
        $this->assertSame($updatedPost->title, $data['title']);
        $this->assertSame($updatedPost->content, $data['content']);
        $this->assertSame($tagIds, $updatedPost->postTags()->get()->pluck('tag_id')->all());
        $this->assertDatabaseHas('post_histories',
            ['post_id' => $this->post->id, 'title' => $updatedPost->title, 'content' => $updatedPost->content]);

        //不修改tagIds
        $this->assertTrue($this->postRepository->update($this->postOwnerUser, $this->post->id,
            $this->genUpdatePostData(), $oldTagIds));
        $updatedPost = $this->postRepository->getPostById($this->post->id);
        $this->assertSame($oldTagIds, $updatedPost->tags->pluck('id')->all());

        //修改部分tagIds
        $updatedTagIds = [$oldTagIds[0], $tagIds[0]];
        $this->assertTrue($this->postRepository->update($this->postOwnerUser, $this->post->id,
            $this->genUpdatePostData(), $updatedTagIds));
        $updatedPost = $this->postRepository->getPostById($this->post->id);
        $this->assertSame($updatedTagIds, $updatedPost->tags->pluck('id')->all());
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
}
