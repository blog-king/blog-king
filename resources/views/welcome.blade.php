@if(\Illuminate\Support\Facades\Auth::check())
    {{ var_dump(\Illuminate\Support\Facades\Auth::user())}}

@else
    {{ route('login')  }}
@endif
