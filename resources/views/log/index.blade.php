@extends('admin.layouts.app')
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

    <!-- Include jQuery and Datatables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        #open-modal-btn {
            display: none;
        }

        #settings-modal {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid card ">
        <div class ="col-md-10 offset-md-1 card-body">
            <div class="card-body">
                <table class="table table-striped" id="logs">
                    <thead>
                        <tr>
                            <th>id</th>
                            <th>CRM OrderID </th>
                            <th>Dropshipzone OrderID</th>
                            <th>Dropshipzone Response</th>
                            <th>CRM Response</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    </div>



    <!-- Payload Modal -->
    <div class="modal fade" id="payloadModal" tabindex="-1" role="dialog" aria-labelledby="payloadModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payloadModalLabel">Details</h5>
                    <button type="button" onclick="closeModal()" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                    <code id="payloadContent"></code>
                </div>
            </div>
        </div>
    </div>
    <script>
        var logs = null;
        (function($) {
            "use strict";

            $(function() {
                logs = $('#logs').DataTable({
                    processing: true,
                    responsive: true,
                    serverSide: true,
                    ajax: ({
                        url: '{{ route('admin.log.get-table-data') }}',
                        method: "POST",
                        data: function(d) {
                            d._token = '{{ csrf_token() }}'

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
                            data: "crm_order_id",
                            name: "crm_order_id"
                        },
                        {
                            data: "dropshipzone_order_id",
                            name: "dropshipzone_order_id"
                        },

                        {
                            data: "dropshipzone_response",
                            name: "dropshipzone_response",
                            render: function(data, type, row) {
                                if (type === 'display') {
                                    return '<button class="btn btn-sm btn-info dropshipzone-response" data-response="' +
                                        data + '">View Response</button>';
                                }
                                return data;
                            }
                        },
                        {
                            data: "crm_webhook_response",
                            name: "crm_webhook_response",
                            render: function(data, type, row) {
                                if (type === 'display') {
                                    return '<button class="btn btn-sm btn-info crm-webhook-response" data-response="' +
                                        data + '">View Webhook Response</button>';
                                }
                                return data;
                            }
                        },
                        {
                            data: "action",
                            name: "action",
                        },
                    ],
                    "responsive": true,
                    "bStateSave": true,
                    "bAutoWidth": false,
                    "ordering": false,
                    "searching": true,

                    drawCallback: function() {
                        $(".dataTables_paginate > .pagination").addClass("pagination-bordered");
                    }
                });



            });

        })(jQuery);
    </script>
    <script>
        $(document).on('click', '.dropshipzone-response,.crm-webhook-response', function() {
            var payload = $(this).data('response');
            let stack_trace_txt = payload;
            console.log(payload);
            try {
                stack_trace_txt = JSON.parse(stack_trace_txt);
            } catch (e) {
                console.log(e);
            }
            try {
                stack_trace_txt = JSON.stringify(stack_trace_txt, '', 4);
            } catch (e) {
                console.log(e);
            }

            $('#payloadContent').html(`<pre>${stack_trace_txt}</pre>`);
            $('#payloadModal').modal('show');
        });



        function closeModal() {
            $('#payloadModal').modal('hide');
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    function confirmDelete(id, user_id, url) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send DELETE request via AJAX
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}', // Add CSRF token
                        id:id,
                        user_id: user_id // Pass user_id in the request payload
                    },
                    success: function(response) {
                        if(response.code == 400)
                        {
                            Swal.fire(
                                'Deleted!',
                                'The order has been deleted.',
                                'success'
                            ).then(() => {
                                // Optional: Reload the page or datatable
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'There was an issue deleting the order.',
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
@endsection
