@extends('layout')

@section('nav')
    <li class="nav-item">
        <a class="nav-link" href="#">Link</a>
    </li>
@stop

@section('content')
    <div class="container-xl" style="margin-top: 34px;">
        <div class="jumbotron">
            <h1 class="display-4">Hello, world!</h1>
            <p class="lead">This is a simple hero unit, a simple jumbotron-style component for calling extra attention to featured content or information.</p>
            <hr class="my-4">
            <p>It uses utility classes for typography and spacing to space content out within the larger container.</p>
            <a class="btn btn-primary btn-lg" href="#" role="button">Learn more</a>
        </div>
    </div>
@endsection
