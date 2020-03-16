<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? config('app.name') }}</title>
    @include('bootstrap')
    <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
    @yield('header')
</head>
<body>
@include('navbar')

@yield('content')
</body>
</html>
