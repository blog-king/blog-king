<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? config('app.name') }}</title>
    @include('bootstrap')
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    @yield('header')
</head>
<body class="d-flex flex-column h-100">

<header>
    <nav id="navbar-nav" class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
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
                @yield('nav')
            </ul>

            <ul class="navbar-nav mr-right">
                @if ($user = auth()->user())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ $user->name }}
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="#">个人中心</a>
                            <a class="dropdown-item" href="{{ route('blog', ['name'=>$user->name]) }}">我的博客</a>
                            <a class="dropdown-item" href="#">我的收藏</a>
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('logout') }}" method="post">
                                {{ csrf_field() }}
                                <button type="submit" class="dropdown-item">退出登录</button>
                            </form>
                        </div>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('login', ['platform'=>\App\Models\Oauth::PLATFORM_GITHUB]) }}">登录</a>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
</header>

<main role="main" id="main" class="flex-shrink-0">
    @yield('content')

</main>

@yield('footer')
</body>
</html>
