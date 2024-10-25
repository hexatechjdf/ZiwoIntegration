<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <h4 class="h4">Dropshipzone Credentials
            </h4>
        </div>
        <div class="card-body">

            <div class="col-md-12" id="dropshipzoneFormContainer" style="">
                <form id="dropshipzoneForm">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label for="email"> Email</label>
                            <input type="email" class="form-control " value="{{ $settings['email'] ?? '' }}"
                                id="email" name="setting[email]" aria-describedby="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password"> Password</label>
                            <input type="password" class="form-control " value="{{ $settings['password'] ?? '' }}"
                                id="password" name="setting[password]" aria-describedby="password" required>
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
<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <h4 class="h4">Default Setting</h4>
        </div>
        <div class="card-body">
            <!-- Toggle Switch -->
            <div class="form-group">
                <label>Enable Extra Profit on RRP</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="toggleSwitch"
                        {{ !empty($settings['extra_percentage']) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="toggleSwitch">On/Off</label>
                </div>
            </div>

            <form id="setting">

                <div class="modal-body">
                    @csrf
                    @include('layouts.discount')
                </div>
                <div class="modal-footer my-2">
                    <button type="submit" id="setting_submit" class="btn btn-primary">Submit</button>
                </div>
            </form>

        </div>
    </div>
</div>
@include('layouts.script')
<script>
    $(document).ready(function() {
        $('#dropshipzoneForm,#setting').on('submit', function(e) {
            e.preventDefault();
            var data = $(this).serialize();
            var url = '{{ route('admin.integration.save') }}';
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
