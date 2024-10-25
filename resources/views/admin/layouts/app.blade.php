<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
     <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

    <!-- Include jQuery and Datatables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    @yield('css')
    <style>
        a.nav-link {
            width: 120px;
        }

        a.nav-link.active {
            color: white !important;
            background: black;
            border-radius: 8px;
            padding-left: 31px !important;
        }
    </style>
</head>

<body>
    <div id="app">

        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'DropShippingZones') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @php
                            $isAdmin = loginUser()->role == 1;
                        @endphp
                        @if ($isAdmin)
                            <li class="nav-item">
                                <a class="nav-link  @if (request()->url() == route('admin.setting')) active @endif "
                                    href="{{ route('admin.setting') }}">Setting</a>
                            </li>
                              <li class="nav-item">
                                <a class="nav-link  @if (request()->url() == route('admin.ziwo.index')) active @endif "
                                    href="{{ route('admin.ziwo.index') }}">Ziwo</a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link  @if (request()->url() == route('admin.location.index')) active @endif "
                                    href="{{ route('admin.location.index') }}">Locations</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link @if (request()->url() == route('admin.integration')) active @endif"
                                    href="{{ route('admin.integration') }}">Integration</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link  @if (request()->url() == route('admin.product.index')) active @endif"
                                    href="{{ route('admin.product.index') }}">Product</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link  @if (request()->url() == route('admin.logs.index')) active @endif"
                                    href="{{ route('admin.logs.index') }}">Logs</a>
                            </li>
                        @endif
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            @if ($isAdmin)
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        {{ Auth::user()->name }}
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        {{-- <a class="dropdown-item" href="">
                                            {{ Auth::user()->email }}
                                </a>
                                <a type="button" class="dropdown-item btn btn-primary" id="openModal">
                                    {{ __('My Profile') }}
                                </a> --}}
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                            {{ __('Logout') }}
                                        </a>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                            class="d-none">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @endif
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header  d-flex justify-content-between">
                        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="formSubmit">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="exampleInputPassword1" class="form-label">UserName</label>
                                <input type="text" name="username" class="form-control"
                                    value="{{ Auth::user()->name }}">
                            </div>
                            <div class="mb-3">
                                <label for="exampleInputEmail1" class="form-label">Email address</label>
                                <input type="email" name="email" class="form-control" id="example"
                                    aria-describedby="emailHelp" value="{{ Auth::user()->email }}">
                                <input type="hidden" name="user_id" class="form-control" id="user_id"
                                    value="{{ Auth::user()->id }}">
                            </div>
                            <div class="mb-3">
                                <label for="exampleInputPassword1" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" id="password">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" id="closeModal">Close</button>
                            <button type="submit" id="submitButton" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


   <div class="modal fade" id="ajaxModal" tabindex="-1" aria-labelledby="ajaxModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ajaxModalLabel">Connection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      </div>
    </div>
  </div>
</div>




    @include('autoauth.cdn')
    @include('language')
    @include('autoauth.message', ['script' => 1])
    @yield('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).on('click', '.ajax-modal', function(e) {
            e.preventDefault(); // Prevent default link behavior
            var url = $(this).attr('href'); // Get the URL from the link

            // Load the content into the modal body
            $('#ajaxModal .modal-body').load(url, function() {
                $('#ajaxModal').modal('show'); // Show the modal after content is loaded
            });
        });
    </script>
</body>

</html>
