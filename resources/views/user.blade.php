@extends('layout')

@section('header')
    <link rel="stylesheet" href="{{ asset('css/user.css') }}">
    <script src="{{ asset('js/user.js') }}"></script>
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
                <div id="user-tags" class="p-3">
                    <p>标签</p>
                    @if (empty($tags))
                        <p class="nothing">
                            暂无标签，您可以
                            <button class="btn btn-sm btn-primary">添加标签</button>
                        </p>
                    @else
                        @foreach($tags as$tag)
                            <span>{{ $tag }}</span>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="col rounded-sm user-module p-3">
                右边的内容
            </div>
        </div>
    </div>
@stop
