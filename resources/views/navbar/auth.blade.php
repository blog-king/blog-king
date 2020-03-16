@if ($user = auth()->user())
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{ $user->name }}
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="#">个人中心</a>
            <a class="dropdown-item" href="#">我的博客</a>
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