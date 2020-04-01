<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user($name, Request $request)
    {
        /** @var User $user */
        $user = User::query()->where('name', $name)->firstOrFail();
        $posts = $user->posts()
            ->with('tags')
            ->where('privacy', Post::STATUS_PUBLISH)
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('user', [
            'title' => $name.'的主页',
            'user' => $user,
            'posts' => $posts,
        ]);
    }
}
