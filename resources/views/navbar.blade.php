<div id="navbar">
    <div class="container-xl">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand" href="{{ route('homepage') }}">
                <img src="https://v4.bootcss.com/docs/assets/brand/bootstrap-solid.svg" width="30" height="30" alt="">
                {{ config('app.name') }}
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('homepage') }}">主页 <span class="sr-only">(current)</span></a>
                    </li>
                    @yield('nav')
                </ul>

                <ul class="navbar-nav mr-right">
                    @if ($user = auth()->user())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-sm">
                                {{ $user->name }}
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">个人中心</a>
                                <a class="dropdown-item" href="#">我的博客</a>
                                <a class="dropdown-item" href="#">我的收藏</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">退出登录</a>
                            </div>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">登录</a>
                        </li>
                    @endif
                </ul>
            </div>
        </nav>
    </div>
</div>
