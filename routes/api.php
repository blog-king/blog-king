<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//posts
Route::group(['middleware' => ['auth']], function () {
    Route::patch('post/{id}', 'PostsController@update')->name('post-api-update'); //修改文章
    Route::delete('post/{id}', 'PostsController@delete')->name('post-api-delete'); //修改文章
    Route::post('post', 'PostsController@create')->name('post-api-create'); //创建文章
});

Route::get('post/{id}', 'PostsController@show')->name('post-api-show'); //查看文章
Route::get('posts', 'PostsController@postsList')->name('post-api-list'); //查看文章列表


//tags
Route::get('tags', 'TagsController@tags')->name('tags-api-list'); //获取tags
