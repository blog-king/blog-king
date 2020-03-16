@extends('layout')

@section('header')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <script>
        $(function () {
            $('.jumbotron').css({height: $(window).height() + 'px'})
            $('body').scrollspy({target: '#navbar-nav'})
        });
    </script>
@stop

@section('content')

    <div id="navbar" class="fixed-top">
        <div class="container-xl position-sticky">
            <nav id="navbar-nav" class="navbar navbar-expand-lg navbar-dark bg-dark">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <img src="https://v4.bootcss.com/docs/assets/brand/bootstrap-solid.svg" width="30" height="30" alt="">
                    {{ config('app.name') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto">

                        <li class="nav-item">
                            <a class="nav-link" href="#styles">风格</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#features">功能</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#tests">测试</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#api">API</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#open-source">开源</a>
                        </li>
                    </ul>

                    <ul class="navbar-nav mr-right">
                        @include('navbar.auth')
                    </ul>
                </div>
            </nav>
        </div>
    </div>



    <div class="container-xl" data-spy="scroll" data-target="#navbar-nav" data-offset="0">

        <div class="jumbotron banner" id="styles">
            <h1 class="display-4">简洁的样式</h1>
            <p class="lead">我们采用 bootstrap 对我们的页面进行样式开发</p>
            <hr class="my-4">
            <p>It uses utility classes for typography and spacing to space content out within the larger container.</p>
            <a class="btn btn-primary btn-lg" href="#" role="button">如何自定义样式 ？</a>
        </div>

        <div class="jumbotron banner" id="features">
            <h1 class="display-4">完善的功能</h1>
            <p class="lead">我们采用 bootstrap 对我们的页面进行样式开发</p>
            <hr class="my-4">
            <p>It uses utility classes for typography and spacing to space content out within the larger container.</p>
            <a class="btn btn-primary btn-lg" href="#" role="button">Learn more</a>
        </div>

        <div class="jumbotron banner" id="tests">
            <h1 class="display-4">测试用例</h1>
            <p class="lead">测试用例覆盖范围达到 95%</p>
            <hr class="my-4">
            <p>It uses utility classes for typography and spacing to space content out within the larger container.</p>
            <a class="btn btn-primary btn-lg" href="#" role="button">phpunit</a>
        </div>

        <div class="jumbotron banner" id="api">
            <h1 class="display-4">开放的API</h1>
            <p class="lead">我们的API是完全开放的，这意味着您可以自己开发自己的博客客户端程序，然后用我们 Blog-king 的API来完善博客功能。</p>
            <hr class="my-4">
            <p>这对有前端开发能力的用户来说是非常友好，因为我们的API是允许跨域的。</p>
            <a class="btn btn-primary btn-lg" href="#api" role="button">阅读 API 文档</a>
        </div>

        <div class="jumbotron banner" id="open-source">
            <h1 class="display-4">开源</h1>
            <p class="lead">该项目是完全开源的，在 MIT 协议允许的范围内，您可以任意处置该项目的源代码。</p>
            <hr class="my-4">
            <p>It uses utility classes for typography and spacing to space content out within the larger container.</p>
            <a class="btn btn-primary btn-lg" target="_blank"
               href="https://github.com/blog-king/blog-king/blob/master/license" role="button">阅读协议内容</a>
        </div>

    </div>
@endsection
