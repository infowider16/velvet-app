@extends('layouts.admin')

@section('title', 'Contact List')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@endsection

@section('content')

<style>
    .content-wrapper {
        padding: 20px;
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    }

    .card-body {
        padding: 20px;
        overflow-x: auto;
    }

    .card-title {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #333;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #contact-data-table {
        width: 100% !important;
        border-collapse: collapse;
    }

    #contact-data-table thead th {
        background-color: #f8f9fa;
        color: #333;
        font-weight: 600;
        font-size: 14px;
        padding: 12px 14px;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
    }

    #contact-data-table tbody td {
        padding: 12px 14px;
        vertical-align: top;
        font-size: 14px;
        color: #444;
        border-bottom: 1px solid #f1f1f1;
        white-space: normal !important;
        word-break: break-word;
    }

    #contact-data-table tbody tr:hover {
        background-color: #fafafa;
    }

    .message-content {
        max-width: 250px;
        line-height: 1.5;
        word-break: break-word;
    }

    .full-message-box {
        background: #f9f9f9;
        border: 1px solid #e1e1e1;
        border-radius: 6px;
        padding: 15px;
        max-height: 400px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-break: break-word;
        line-height: 1.6;
        font-size: 14px;
    }

    #messageModal .modal-dialog {
        max-width: 760px;
    }

    #messageModal .modal-content {
        border: none;
        border-radius: 10px;
    }

    #messageModal .modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
    }

    #messageModal .modal-title {
        font-size: 18px;
        font-weight: 600;
    }

    #messageModal .modal-body {
        padding: 20px;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 13px;
    }

    @media (max-width: 768px) {
        .card-title {
            font-size: 18px;
        }

        #contact-data-table thead th,
        #contact-data-table tbody td {
            font-size: 13px;
            padding: 10px;
        }

        .message-content {
            max-width: 180px;
        }
    }
</style>

<div class="content-wrapper">

    <div class="row">

        <div class="col-lg-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <h4 class="card-title">Contact Us List</h4>

                    <div class="table-responsive">

                        <table class="table-responsive table" id="contact-data-table">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User ID</th>
                                    <th>User Name</th>
                                    <th>Login Account</th>
                                    <th>Contact Email</th>
                                    <th>Support Category</th>
                                    <th>Support Message</th>
                                    <th>Image</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Created At</th>
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

    </div>

</div>

<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="messageModalLabel">Message Details</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>

            </div>

            <div class="modal-body">

                <div id="fullMessageText" class="full-message-box"></div>

            </div>

        </div>

    </div>

</div>

@endsection

@section('scripts')

<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

<script>

    let table = $('#contact-data-table').DataTable({

        processing: true,
        serverSide: true,

        order: [], // ✅ disables initial sorting

        ajax: {
            url: "{{ route('admin.contact-list') }}"
        },

        columns: [

            {
                data: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },

            { data: 'user_id' },
            { data: 'name' },
            { data: 'login_account' },
            { data: 'email' },
            { data: 'subject' },

            {
                data: 'message',
                orderable: false
            },

            {
                data: 'image',
                orderable: false,
                searchable: false
            },

            { data: 'date_time' },
            { data: 'status' },
            { data: 'created_at' },

            {
                data: 'action',
                orderable: false,
                searchable: false
            }
        ]
    });

    // View full message
    $(document).on('click', '.view-message-btn', function () {

        let message = $(this).attr('data-message');

        $('#fullMessageText').text(message);

        $('#messageModal').modal('show');
    });

    // Change status
    $(document).on('change', '.change-status', function () {

        let id = $(this).data('id');
        let status = $(this).val();

        $.ajax({

            url: "{{ route('admin.contact.change-status') }}",

            type: "POST",

            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                status: status
            },

            success: function(response) {

                toastr.success(response.message);
                table.ajax.reload(null, false);
            },

            error: function(xhr) {

                toastr.error('Something went wrong');
            }
        });
    });

</script>

@endsection

