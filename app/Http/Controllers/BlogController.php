<?php

namespace App\Http\Controllers;

use App\Repository\Repositories\PostRepository;
use App\Repository\Repositories\TagRepository;
use App\User;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function homepage($name, Request $request, TagRepository $tagRepository, PostRepository $postRepository)
    {
        /** @var User $user */
        $user = User::query()->where('name', $name)->firstOrFail();

        $tags = $tagRepository->getTagsByUser($user);
        $posts = $postRepository->getPostsByUser($user->id);

        return view('blog', [
            'title' => $name.'的博客',
            'user' => $user,
            'tags' => $tags,
            'posts' => $posts,
        ]);
    }
}
