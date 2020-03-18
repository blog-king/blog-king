<?php

namespace App\Http\Controllers;

use App\Models\Tags;
use App\Repository\Repositories\TagRepository;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    /**
     * 根据tagIds获取tag.
     *
     * @apiGroup tag
     *
     * @param TagRepository $tagRepository
     * @param Request       $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @api {GET} /tags 通过tagId 获取tag
     * @apiParam {string} tag_ids 用英文逗号隔开
     * @apiSuccess {object[]} data 返回结果集
     * @apiSuccess {int} data.id tag的id
     * @apiSuccess {string} data.name tag的名字
     * @apiSuccess {object[]} data.children tag的子类，结构同上，id,name
     */
    public function getTagsByIds(TagRepository $tagRepository, Request $request)
    {
        $tagIds = array_filter(explode(',', $request->input('tag_ids')));
        $tags = [];
        if (!empty($tagIds)) {
            $tags = $tagRepository->getTagByIds($tagIds);
        }

        return $this->buildReturnData($tags);
    }

    /**
     * 获取tag分类和tag.
     *
     * @apiGroup tag
     *
     * @param TagRepository $tagRepository
     * @param Request       $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @api {GET} /tags
     *
     * @apiParam {int} parent_id tag的父类id，默认不传则返回第一第二层tag
     * @apiSuccess {Object[]} data 返回的结果集
     * @apiSuccess {int} data.id tag的id
     * @apiSuccess {string} data.name tag的name
     * @apiSuccess {array} data.children tag的子类，结构如上，id,name，children
     */
    public function tags(TagRepository $tagRepository, Request $request)
    {
        $parentId = (int) $request->input('parent_id');
        if (empty($parentId)) {
            //开始组装叠层
            $tags = $tagRepository->getLevel0andLevel();
            $tags->each(function (Tags $tag) {
                $tag->setVisible(['id', 'name', 'parent_id', 'level', 'children']);
            });
            $tags = $tags->toArray();

            //先取出第一层
            $result = [];
            foreach ($tags as &$tag) {
                $tag['children'] = [];
                0 == $tag['level'] && $result[$tag['id']] = $tag;
            }
            unset($tag);

            //组装第二层
            foreach ($tags as $tag) {
                isset($result[$tag['parent_id']]) && $result[$tag['parent_id']]['children'][] = $tag;
            }

            //结果集返回数组
            $result = array_values($result);
        } else {
            //默认这请求为第三层，不返回children，第一第二层已经上面返回
            $result = $tagRepository->getTagsByParentId($parentId)->toArray();
            foreach ($result as &$tag) {
                $tag['children'] = [];
            }
        }

        return $this->buildReturnData($result);
    }
}
