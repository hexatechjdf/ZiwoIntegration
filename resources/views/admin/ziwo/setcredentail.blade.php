<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <h4 class="h4">Ziwo  Credentials
            </h4>
        </div>
        <div class="card-body">

            <div class="col-md-12" id="ziwoFormContainer" style="">
                <form id="ziwoForm">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label for="email"> UserName</label>
                            <input type="text" class="form-control " value="{{ $ziwo_details->username ?? '' }}"
                                id="username" name="username" aria-describedby="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password"> Password</label>
                            <input type="password" class="form-control " value="{{ $ziwo_details->password ?? '' }}"
                                id="password" name="password" aria-describedby="password" required>
                        </div>
                         <div class="form-group">
                            <label for="email">Base Endpoint</label>
                            <input type="text" class="form-control " value="{{ $ziwo_details->endpoint ?? '' }}"
                                id="endpoint" name="endpoint" aria-describedby="endpoint" required>
                        </div>
                    </div>
                    <div class="modal-footer my-2">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>

    </div>



</div>
@include('layouts.script')
<script>
    $(document).ready(function() {
        $('#ziwoForm,#setting').on('submit', function(e) {
            e.preventDefault();
            var data = $(this).serialize();
            var url = '{{ route('admin.ziwo.save') }}';
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
</script>
<script>
    $(document).ready(function() {
        // Initial setup based on toggle state
        toggleFields($('#toggleSwitch').is(':checked'));
        let setting_submit = document.querySelector('form#setting');
        setting_submit.addEventListener('submit', function(event) {
            event.preventDefault();

            if (!validateForm()) {
                return;
            }
        });
        // Handle toggle switch change event
        $('#toggleSwitch').change(function() {
            toggleFields($(this).is(':checked'));
        });

        function toggleFields(isChecked) {
            if (isChecked) {
                $('#setting').removeClass('d-none'); // Show the password field
            } else {
                $('#setting').addClass('d-none'); // Hide the password field
                $('#setting').val(''); // Clear the password field value
            }

        }
    });
</script>
