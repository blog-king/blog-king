<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function homepage($name, Request $request)
    {
        // todo: 需要加载更多的用户博客数据
        return view('blog', ['title' => $name.'的博客']);
    }
}
