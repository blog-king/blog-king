<?php

namespace App\Http\Controllers;

use App\Repository\Repositories\PostRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function postList(PostRepository $postRepository, Request $request)
    {
        $userId = Auth::id();
    }
}
