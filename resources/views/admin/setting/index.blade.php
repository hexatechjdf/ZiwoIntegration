@extends('admin.layouts.app')
@section('content')
    <div class="container">

        <div class="row">

            <div class="col-md-6">
                <form id="submitForm" method="POST">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h4 class="h4">CRM OAuth Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>Redirect URI - add while creating app</h6>
                                    <p class="h6"> {{ route('crm.oauth_callback') }} </p>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>Scopes - select while creating app</h6>
                                    <p class="h6"> {{ \CRM::$scopes }} </p>
                                </div>

                            </div>
                            <div class="row mt-2">


                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="clientID" class="form-label"> Client ID</label>
                                        <input type="text" class="form-control "
                                            value="{{ $settings['crm_client_id'] ?? '' }}" id="crm_client_id"
                                            name="setting[crm_client_id]" aria-describedby="clientID" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label for="clientID" class="form-label"> Client secret</label>
                                    <input type="text" class="form-control "
                                        value="{{ $settings['crm_client_secret'] ?? '' }}" id="crm_secret_id"
                                        name="setting[crm_client_secret]" aria-describedby="secretID" required>
                                </div>
                            </div>
                             <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="crm_conversation_provider_id" class="form-label"> CRM Conversation Provider ID</label>
                                        <input type="text" class="form-control "
                                            value="{{ $settings['crm_conversation_provider_id'] ?? '' }}" id="crm_conversation_provider_id"
                                            name="setting[crm_conversation_provider_id]" aria-describedby="crm_conversation_provider_id" required>
                                    </div>
                                </div>
                            <div class="row">
                                <div class="col-md-12 m-2">
                                    <button id="form_submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="h4">Connect To Agency</h4>
                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <label class="control-label">CRM OAuth Company Connection</label>
                            @if ($user && $user->companyCrmAuth?->access_token == '')
                             <a href="https://app.gohighlevel.com/integration/{{ env('MARKETPLACE_APPID') }}"
                                    class="btn btn-primary">Connect to CRM Company</a>
                                {{-- <a href="{{ route('oauthcrmconnection') }}/agency"
                                    class="btn ajax-modal btn-primary">Connect to CRM Company</a> --}}
                            @else
                                <br />
                                Already Connected to Company -
                                <a href="{{ route('oauth.disconnect', 'crmagency') }}" class="btn btn-danger  btn-remove-2"
                                    data-confirm-button="Yes disconnect"
                                    data-text="Once disconnect, unable to create location or user within company">Disconnect
                                    Company</a>
                            @endif
                        </div>
                    </div>

                </div>

                <div class="card mt-2">
                    <div class="card-header">
                        <h4 class="h4">Tag & Logs Setting</h4>
                    </div>
                    <div class="card-body">
                        <form id="submitTagForm">
                            @csrf
                            <div class="mb-3">
                                <label for="username" class="form-label">Enter Misscall Tag</label>
                                <input type="text" name="setting[miss_call_tag_name]" class="form-control" value="{{ $settings['miss_call_tag_name'] ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label for="days" class="form-label" >Please specify the minimum number of days for Call Logs:</label><br>
                                <input class="form-control" type="number" id="days" name="setting[call_logs_days]" value="{{ $settings['call_logs_days'] ?? '3' }}" min="1" required>
                            </div>
                            <button type="submit" id="misscallsubmitButton" class="btn btn-primary">Save</button>

                        </form>

                    </div>

                </div>
                 <div class="card mt-2">
                    <div class="card-header">
                        <h4 class="h4">Default Main Location</h4>
                    </div>
                    <div class="card-body">
                        <form id="submitMainLocationForm">
                            @csrf
                            <div class="mb-3">
                                <label for="username" class="form-label">Enter Location Id </label>
                                <input type="text" name="setting[agency_main_location]" class="form-control" value="{{ $settings['agency_main_location'] ?? '' }}">
                            </div>
                            <button type="submit" id="misscallsubmitButton" class="btn btn-primary">Save</button>

                        </form>

                    </div>

                </div>
            </div>

        </div>
        <div class="row mt-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="h4">CRM Custom Menu Link for Auto Login</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <ul>
                            <li>Add below url to custom menu link enabled only for locations as an iframe.</li>
                        </ul>
                    </div>
                    <div class="copy-container">
                        <input type="text" class="form-control code_url"
                            value="{{ route('auth.check') }}?location_id={{ braceParser('[[location.id]]') }}&sessionkey={{ braceParser('[[user.sessionKey]]') }}"
                            readonly>
                        <div class="row my-2">
                            <div class="col-md-12" style="text-align: left !important">
                                <button type="button" class="btn btn-primary script_code" data-message="Link Copied"
                                    id="kt_account_profile_details_submit">Copy URL</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="card">
                    <div class="card-header">
                        <h4 class="h4">Update Profile</h4>
                    </div>
                    <div class="card-body">
                        <div class="col-md-8">
                            <div class="copy-container">
                                <form id="formSubmit">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Enter UserName</label>
                                        <input type="text" name="username" class="form-control"
                                            value="{{ Auth::user()->name }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Enter Email</label>
                                        <input type="email" name="email" class="form-control" id="example"
                                            aria-describedby="emailHelp" value="{{ Auth::user()->email }}">
                                        <input type="hidden" name="user_id" class="form-control" id="user_id"
                                            value="{{ Auth::user()->id }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Enter Password</label>
                                        <input type="password" name="password" class="form-control" id="password">
                                    </div>
                                    <button type="submit" id="submitButton" class="btn btn-primary">Save</button>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                $("body").on('click', '.script_code', function(e) {
                    e.preventDefault();
                    let msg = $(this).data('message');
                    var url = $(this).closest('.copy-container').find('.code_url').val();
                    if (url) {
                        navigator.clipboard.writeText(url).then(function() {
                            toastr.success(msg, {
                                timeOut: 10000
                            });
                        }, function() {
                            toastr.error("Error while Copy", {
                                timeOut: 10000
                            });
                        });
                    } else {
                        toastr.error("No data found to copy", {
                            timeOut: 10000
                        });
                    }
                });

                $(document).ready(function() {
                    $('#submitForm,#submitMainLocationForm').on('submit', function(e) {
                        e.preventDefault();
                        var data = $(this).serialize();
                        var url = '{{ route('admin.setting.save') }}';
                        $.ajax({
                            type: 'POST',
                            url: url,
                            data: data,
                            success: function(response) {


                                try {
                                    toastr.success('Saved');
                                } catch (error) {
                                    alert('Saved');
                                }
                                console.log('Data saved successfully:', response);
                            },
                            error: function(xhr, status, error) {

                                console.error('Error saving data:', error);
                            }
                        });
                    });
                    $(document).ready(function() {
                        $('#openModal').click(function() {
                            $('#exampleModal').modal('show');
                        });

                        $('#closeModal').click(function() {
                            $('#exampleModal').modal('hide');
                        });

                        $('#formSubmit').on('submit', function(e) {
                            e.preventDefault();
                            var formData = $(this).serialize();
                            var token = $('meta[name="csrf-token"]').attr('content');
                            var userId = $('[name="user_id"]').val();
                            $.ajax({
                                type: "PUT",
                                url: "{{ route('admin.user.profile', ['id' => ':id']) }}".replace(
                                    ':id', userId),
                                headers: {
                                    'X-CSRF-TOKEN': token
                                },
                                data: formData, // Send serialized form data
                                success: function(result) {
                                    if (result.status === 'Success') {
                                        $('#exampleModal').modal('hide');
                                        toastr.success(result.message);
                                    } else {
                                        toastr.error('Error: ' + result.message);
                                        console.error('Error:', result.message);
                                    }
                                },
                                error: function(xhr) {
                                    var error = JSON.parse(xhr.responseText);
                                    toastr.error('Error: ' + error.message);
                                    console.error('Error:', error.message);
                                }
                            });
                        });
                        $('#submitTagForm').on('submit', function(e) {
                        e.preventDefault();
                        var data = $(this).serialize();
                        var url = '{{ route('admin.setting.save') }}';
                        $.ajax({
                            type: 'POST',
                            url: url,
                            data: data,
                            success: function(response) {


                                try {
                                    toastr.success('Saved');
                                } catch (error) {
                                    alert('Saved');
                                }
                                console.log('Data saved successfully:', response);
                            },
                            error: function(xhr, status, error) {

                                console.error('Error saving data:', error);
                            }
                        });
                    });
                    });
                });
            </script>
        @endsection
