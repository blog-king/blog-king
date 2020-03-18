<?php

namespace Tests\Feature;

use App\Models\Tags;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TagsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testApiForTags()
    {
        $parentTagCount = 3;
        $childTagCount = 10;
        factory(Tags::class, 1)->create(['name' => 'Painting'])->each(function (Tags $tag) use ($parentTagCount, $childTagCount) {
            factory(Tags::class, $parentTagCount)->create([
                'name' => 'color'.$tag->id,
                'parent_id' => $tag->id,
                'level' => $tag->level + 1,
            ])->each(function (Tags $childTag) use ($childTagCount) {
                factory(Tags::class, $childTagCount)->create([
                    'parent_id' => $childTag->id,
                    'level' => $childTag->level + 1,
                ]);
            });
        });

        $response = $this->get(route('tags-api-list'));
        $response->assertOk();
        $tag = $response->json()['data'][0];
        $this->assertArrayHasKey('id', $tag);
        $this->assertArrayHasKey('name', $tag);
        $this->assertArrayHasKey('children', $tag);
        $this->assertIsArray($tag['children']);
        $this->assertSame($parentTagCount, count($tag['children']));

        //第二层的第一个parentId
        $parentTagId = $tag['children'][0]['id'];
        $response = $this->get(route('tags-api-list', ['parent_id' => $parentTagId]));
        $response->assertOk();
        $content = $response->json()['data'];
        $tag = $content[0];
        $this->assertArrayHasKey('id', $tag);
        $this->assertArrayHasKey('name', $tag);
        $this->assertSame($childTagCount, count($content));
    }
}
