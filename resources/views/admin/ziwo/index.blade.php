@extends('admin.layouts.app')
@section('content')
    <div class="container">
        <div class="row justify-content-center">

            @include('admin.ziwo.setcredentail')


        </div>
    </div>
    @include('autoauth.cdn')

@endsection
