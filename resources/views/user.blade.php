@extends('layout')

@section('header')
    <link rel="stylesheet" href="{{ asset('css/user.css') }}">
    <script>

        $(function () {
            const tabsBtn = $('.tabs');
            const rightContent = $('.right-content');

            tabsBtn.on({
                'click': function () {
                    rightContent.hide();
                    $('.tabs.active').removeClass('active');
                    $(this).addClass('active');
                    $('#' + $(this).attr('data-toggle')).show();
                }
            });

        });
    </script>
@stop

@section('content')
    <div class="container-xl mt-3 h-100">
        <div class="row p-md-3">
            <div class="col col-md-4 col-lg-4 col-xl-4 col-12 rounded-sm user-module mr-3 p-3">
                <div id="user-header" class="d-flex flex-column align-items-center">
                    <img id="user-avatar" class="rounded-circle" src="{{ $user->avatar }}" alt="{{ $user->name }}">
                    <h3 class="font-weight-bold">{{ $user->nickname }}</h3>
                    <p class="font-weight-lighter text-wrap" title="{{ $user->introduction }}">
                        {{ $user->introduction }}
                    </p>
                </div>
                <div id="user-title" class="p-3">
                    @if($user->name)
                        <div class="user-title-item">
                            <p>
                                <span>博客:</span> {{ $user->title ?: '未设置博客标题' }}
                            </p>
                        </div>
                        <div class="user-title-item">
                            <p>
                                <span>ID:</span> {{ $user->name }}
                            </p>
                        </div>
                        <div class="user-title-item">
                            <button class="btn btn-sm btn-primary">修改资料</button>
                        </div>
                    @else
                        <div id="nonactivated" class="p-3 text-center">
                            您暂未开通博客，
                            <button class="btn btn-sm btn-primary">点击开通</button>
                        </div>
                    @endif
                </div>
                <div class="line"></div>
                <div id="user-tags" class="p-3 d-flex">
                    <div class="concern flex-fill">
                        <span>订阅了</span>
                        <p>{{ $user->concerns_count ?? 0 }}人</p>
                    </div>
                    <div class="border-left fans flex-fill">
                        <span>粉丝</span>
                        <p>{{ $user->fans_count ?? 0 }}人</p>
                    </div>
                </div>
            </div>
            <div class="col rounded-sm user-module p-3">
                <div class="btn-group mb-3" role="group" aria-label="按钮组">
                    <button type="button" data-toggle="posts" class="active tabs btn btn-secondary">文章</button>
                    <button type="button" data-toggle="carousel" class="tabs btn btn-secondary">轮播</button>
                    <button type="button" data-toggle="data" class="tabs btn btn-secondary">数据</button>
                </div>

                <div id="carousel" class="right-content">
                </div>
                <div id="data" class="right-content">
                </div>
                <div id="posts" class="right-content">
                    @if (empty($posts))
                        <div class="alert alert-primary" role="alert">
                            您暂未发布任何文章，快去 <a href="{{ route('post.create') }}" class="btn"></a>
                        </div>
                    @else
                        @foreach($posts as $post)
                            <div class="row no-gutters border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
                                <div class="col p-4 d-flex flex-column position-static">
                                    <strong class="d-inline-block mb-2 text-primary">{{ $post->tags->count() > 0 ? implode(',', $post->tags->pluck('name')->toArray()) : '无标签' }}</strong>
                                    <h3 class="mb-0">{{ $post->title }}</h3>
                                    <div class="mb-1 text-muted">{{ $post->created_at->diffForHumans() }}</div>
                                    <p class="card-text mb-auto">{{ $post->description }}</p>
                                    <a href="#" class="stretched-link">继续阅读</a>
                                </div>
                                <div class="col-auto d-none d-lg-block align-self-center">
                                    @if ($post->thumbnail)
                                        <img class="post-thumbnail img-thumbnail" src="{{ $post->thumbnail }}"
                                             alt="{{ $post->title }}">
                                    @else
                                        <svg class="bd-placeholder-img" width="200" height="250"
                                             xmlns="http://www.w3.org/2000/svg"
                                             preserveAspectRatio="xMidYMid slice" focusable="false" role="img"
                                             aria-label="Placeholder: Thumbnail"><title>{{ $post->title }}</title>
                                            <rect width="100%" height="100%" fill="#55595c"/>
                                            <text x="50%" y="50%" fill="#eceeef" dy=".3em">封面</text>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
