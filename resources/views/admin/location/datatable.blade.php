<script>
    var location_table = null;
    (function($) {
        "use strict";

        $(function() {
            if ($.fn.DataTable.isDataTable('#location_list')) {
                $('#location_list').DataTable().destroy();
            }

            location_table = $('#location_list').DataTable({
                processing: true,
                serverSide: true,
                ajax: ({
                    url: '{{ route('admin.location.table.data') }}',
                    method: "POST",
                    data: function(d) {
                        d._token = '{{ csrf_token() }}',
                            d.location_id = $('select[name=location_id]').val();
                    },
                    error: function(request, status, error) {
                        console.log(request.responseText);
                    }
                }),
                "columns": [{
                        data: "id",
                        name: "id"
                    },
                    {
                        data: "name",
                        name: "name"
                    },
                    {
                        data: "email",
                        name: "email"
                    },
                    {
                        data: "location_id",
                        name: "location_id"
                    },
                      {
                        data: "action",
                        name: "action"
                    },

                ],
                responsive: true,
                "bStateSave": true,
                "bAutoWidth": false,
                "ordering": false,
                "searching": true,
                "language": {
                    "decimal": "",
                    "emptyTable": $lang_no_data_found,
                    "info": $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of +
                        " _TOTAL_ " + $lang_entries,
                    "infoEmpty": $lang_showing_0_to_0_of_0_entries,
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": $lang_show + " _MENU_ " + $lang_entries,
                    "loadingRecords": $lang_loading,
                    "processing": $lang_processing,
                    "search": $lang_search,
                    "zeroRecords": $lang_no_matching_records_found,
                    "paginate": {
                        "first": $lang_first,
                        "last": $lang_last,
                        "previous": "<i class='ti-angle-left'></i>",
                        "next": "<i class='ti-angle-right'></i>"
                    }
                },
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-bordered");
                }
            });
        });

    })(jQuery);
    $('body').on("change", "#location_id", function(e) {
        location_table.draw();
    });
$(document).on('click', '.btn-toggle-integration', function() {
    var button = $(this);
    var locationId = button.data('id');
    var currentStatus = button.data('status');

    // Make an AJAX call to toggle the integration status
    $.ajax({
        url: '/api/locations/toggle-integration/' + locationId, // Adjust the API URL as needed
        method: 'POST',
        data: {
            status: currentStatus,
            _token: '{{ csrf_token() }}' // Include CSRF token for security
        },
        success: function(response) {
            if (response.success) {
                // Update button text and status data
                button.data('status', currentStatus === 'on' ? 'off' : 'on');
                button.text(currentStatus === 'on' ? 'Integration Off' : 'Integration On');
            } else {
                alert('Failed to update integration status');
            }
        },
        error: function() {
            alert('Error occurred while toggling integration status');
        }
    });
});

</script>
