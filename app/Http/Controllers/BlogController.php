<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function homepage($name, Request $request)
    {
        return view('blog', ['title' => $name.'的博客']);
    }
}
