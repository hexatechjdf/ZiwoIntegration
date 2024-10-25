@extends('admin.layouts.app')
@section('content')
    <div class="container">

   <div class="card-body">
                <table id="location_list" class="table data-table">
                    <thead>
                        <tr>
                            <th class="text-center">Id</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Location Id</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
    </div>

            @include('autoauth.cdn')


    @endsection

@section('script')
@include('admin.location.datatable')

@endsection
