<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\User;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function homepage($name, Request $request)
    {
        /** @var User $user */
        $user = User::query()->where('name', $name)->firstOrFail();

        // todo: 改成获取用户相关的 tags
        $tags = Tag::query()->where('parent_id', 0)->get();

        $user->posts()->visible()->limit(10)->orderByDesc('id')->get();

        return view('blog', [
            'title' => $name.'的博客',
            'user' => $user,
            'tags' => $tags,
        ]);
    }
}
