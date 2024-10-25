@extends('admin.layouts.app')
@section('content')
    <div class="container">
        <div class="row justify-content-center">

            @include('admin.integration.location.index')


        </div>
    </div>
    @include('autoauth.cdn')

@endsection
