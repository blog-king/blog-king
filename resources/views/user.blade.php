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
                    <img class="rounded-circle" src="{{ $user->avatar }}" alt="{{ $user->name }}">
                    <h3 class="font-weight-bold">{{ $user->name }}</h3>
                    <p class="font-weight-lighter">{{ $user->introduction }}</p>
                </div>
            </div>
            <div class="col rounded-sm user-module p-3">
                右边的内容
            </div>
        </div>
    </div>
@stop
