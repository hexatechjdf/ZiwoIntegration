@extends('admin.layouts.app')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">

                    @if ($user->count() > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($user as $users)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>{{ $users->name }}</td>
                                        <td>{{ $users->email }}</td>
                                        <td></td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    @else
                    <p class="text-center">NO Record Found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
