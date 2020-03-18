<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user($name, Request $request)
    {
        // todo: 需要加载更多的用户博客数据

        $user = User::query()->where('name', $name)->first();

        return view('user', ['title' => $name.'的主页', 'user' => $user]);
    }
}
