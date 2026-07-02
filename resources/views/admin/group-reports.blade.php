@extends('layouts.admin')

@section('title', 'Group Reports')

@section('content')

<!-- CSS -->
<style>

    .report-modal {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 10px 35px rgba(0,0,0,0.15);
    }

    .report-modal-header {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: #fff;
        border: none;
        padding: 14px 18px;
    }

    .report-close-btn {
        width: 34px;
        height: 34px;
        border: none;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.2s;
        font-size: 14px;
    }

    .report-close-btn:hover {
        background: #fff;
        color: #17a2b8;
        transform: rotate(90deg);
    }

    .report-card {
        background: #fff;
        border-radius: 10px;
        border: 1px solid #f1f1f1;
        padding: 14px;
        margin-bottom: 14px;
    }

    .report-card-title {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #2d3748;
    }

    .report-card label {
        font-size: 11px;
        font-weight: 700;
        color: #8a8f98;
        margin-bottom: 2px;
        text-transform: uppercase;
    }

    .report-card p {
        font-size: 14px;
        color: #2d3748;
        margin-bottom: 0;
        word-break: break-word;
    }

    .message-box {
        background: #f8fafc;
        border-left: 3px solid #17a2b8;
        border-radius: 8px;
        padding: 10px 12px;
    }

    .reason-box {
        border-left-color: #dc3545;
    }

    #modal-group-image img,
    #modal-image img {
        max-width: 100%;
        max-height: 220px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #eee;
    }

    @media(max-width: 576px){

        .modal-dialog {
            margin: 10px;
        }

        .report-card {
            padding: 12px;
        }

        .report-card p {
            font-size: 13px;
        }
    }

</style>

<div class="content-wrapper px-0">

<div class="content-header">
    <div class="container-fluid">

        <h1 class="m-0 text-info mb-3">
            <i class="fas fa-users mr-2"></i>Group Reports
        </h1>

    </div>
</div>

<section class="content">

    <div class="container-fluid">

        <div class="card shadow-lg border-0">

            <div class="card-header bg-gradient-info text-white">

                <h3 class="card-title mb-0">
                    <i class="fas fa-list mr-2"></i>Reported Groups
                </h3>

            </div>

            <div class="card-body">
                <!-- Filters Section -->
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">

                        <div class="row">

                            <!-- Status -->
                            <div class="col-md-3 mb-3">
                                <label>Status</label>

                                <select class="form-control filter-input"
                                        id="filter-status">

                                    <option value="">All</option>

                                    <option value="Pending">Pending</option>
                                    <option value="Open">Open</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Resolved">Resolved</option>

                                </select>
                            </div>

                            <!-- Date -->
                            <div class="col-md-3 mb-3">
                                <label>Date</label>

                                <input type="date"
                                    class="form-control filter-input"
                                    id="filter-date">
                            </div>

                            <!-- Reason -->
                            <div class="col-md-3 mb-3">
                                <label>Reason</label>

                                <input type="text"
                                    class="form-control filter-input"
                                    id="filter-reason"
                                    placeholder="Search reason">
                            </div>

                            <!-- Reporter -->
                            <div class="col-md-3 mb-3">
                                <label>Reporter</label>

                                <input type="text"
                                    class="form-control filter-input"
                                    id="filter-reporter"
                                    placeholder="Reporter name">
                            </div>

                        </div>

                        <div class="row">

                            <!-- Group -->
                            <div class="col-md-4 mb-3">
                                <label>Group</label>

                                <input type="text"
                                    class="form-control filter-input"
                                    id="filter-group"
                                    placeholder="Group name or ID">
                            </div>

                            <!-- Group Owner -->
                            <div class="col-md-4 mb-3">
                                <label>Group Owner</label>

                                <input type="text"
                                    class="form-control filter-input"
                                    id="filter-owner"
                                    placeholder="Owner name">
                            </div>

                            <!-- Reset -->
                            <div class="col-md-4 mb-3 d-flex align-items-end">

                                <button class="btn btn-secondary"
                                        id="reset-filters">

                                    Reset Filters

                                </button>

                            </div>

                        </div>

                    </div>
                </div>
                <table class="table-responsive table"
                       id="report-data-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Report ID</th>
                            <th>Group Name</th>
                            <th>Group ID</th>
                            <th>Group Owner</th>
                            <th>Members Count</th>
                            <th>Reporter Name</th>
                            <th>Reporter ID</th>
                            <th>Reporter Login</th>
                            <th>Report Type</th>
                            <th>Reason</th>
                            <th>Screenshot</th>
                            <th>Reported At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody></tbody>

                </table>

            </div>

        </div>

    </div>

</section>

<!-- Group Report Detail Modal -->
<!-- Compact Modern Group Report Modal -->
<div class="modal fade" id="groupReportDetailModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-md">

        <div class="modal-content report-modal">

            <!-- Header -->
            <div class="modal-header report-modal-header">

                <div>
                    <h5 class="modal-title mb-0">
                        <i class="fas fa-users mr-2"></i>
                        Group Report
                    </h5>

                    <small class="text-light opacity-75">
                        Group Report Details
                    </small>
                </div>

                <!-- Close Button -->
                <button type="button"
                        class="report-close-btn"
                        data-dismiss="modal"
                        aria-label="Close">

                    x

                </button>

            </div>

            <!-- Body -->
            <div class="modal-body p-3">

                <!-- Group Info -->
                <div class="report-card">

                    <div class="report-card-title">
                        <i class="fas fa-layer-group text-info mr-2"></i>
                        Group Information
                    </div>

                    <div class="row">

                        <div class="col-6 mb-2">
                            <label>Group ID</label>
                            <p id="modal-group-id">-</p>
                        </div>

                        <div class="col-6 mb-2">
                            <label>Group Name</label>
                            <p id="modal-group-name">-</p>
                        </div>

                        <div class="col-6 mb-2">
                            <label>Owner</label>
                            <p id="modal-group-owner">-</p>
                        </div>

                        <div class="col-6 mb-2">
                            <label>Group Type</label>
                            <p id="modal-group-type">-</p>
                        </div>

                        <div class="col-12 mb-2">
                            <label>Members Count</label>
                            <p id="modal-members-count">-</p>
                        </div>

                        <div class="col-12 mt-2">
                            <label>Description</label>

                            <div class="message-box">
                                <p id="modal-group-description" class="mb-0">-</p>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Group Image -->
                <div class="report-card">

                    <div class="report-card-title">
                        <i class="fas fa-image text-warning mr-2"></i>
                        Group Image
                    </div>

                    <div id="modal-group-image" class="text-center"></div>

                </div>

                <!-- Report Info -->
                <div class="report-card">

                    <div class="report-card-title">
                        <i class="fas fa-flag text-danger mr-2"></i>
                        Report Information
                    </div>

                    <div class="row">

                        <div class="col-6 mb-2">
                            <label>Report ID</label>
                            <p id="modal-report-id">-</p>
                        </div>

                        <div class="col-6 mb-2">
                            <label>Report Type</label>
                            <p id="modal-report-type">-</p>
                        </div>

                        <div class="col-12 mb-2">
                            <label>Reported At</label>
                            <p id="modal-created">-</p>
                        </div>

                        <div class="col-12 mt-2">
                            <label>Reason</label>

                            <div class="message-box reason-box">
                                <p id="modal-reason" class="mb-0">-</p>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Reporter Info -->
                <div class="report-card">

                    <div class="report-card-title">
                        <i class="fas fa-user text-success mr-2"></i>
                        Reporter Information
                    </div>

                    <div class="row">

                        <div class="col-6 mb-2">
                            <label>Reporter ID</label>
                            <p id="modal-reporter-id">-</p>
                        </div>

                        <div class="col-6 mb-2">
                            <label>Reporter Name</label>
                            <p id="modal-reporter-name">-</p>
                        </div>

                        <div class="col-12">
                            <label>Reporter Login</label>
                            <p id="modal-reporter-login">-</p>
                        </div>

                    </div>

                </div>

                <!-- Screenshot -->
                <div class="report-card mb-0">

                    <div class="report-card-title">
                        <i class="fas fa-camera text-primary mr-2"></i>
                        Screenshot Evidence
                    </div>

                    <div id="modal-image" class="text-center"></div>

                </div>

            </div>

        </div>

    </div>

</div>


@endsection

@section('scripts')
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<script>

    let table = $('#report-data-table').DataTable({

        processing: true,
        serverSide: true,

        ajax: {
            url: "{{ route('admin.group-reports.list') }}",
            data: function (d) {

                d.status   = $('#filter-status').val();
                d.date     = $('#filter-date').val();
                d.reason   = $('#filter-reason').val();
                d.reporter = $('#filter-reporter').val();
                d.group    = $('#filter-group').val();
                d.owner    = $('#filter-owner').val();
            }
        },

       columns: [

        {
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
        },
        {
            data: 'reported_id',
            name: 'reported_id'
        },

        {
            data: 'group_name',
            name: 'group_name'
        },

        {
            data: 'group_id',
            name: 'group_id'
        },

        {
            data: 'group_owner',
            name: 'group_owner'
        },

        {
            data: 'members_count',
            name: 'members_count'
        },

        {
            data: 'reporter_name',
            name: 'reporter_name'
        },

        {
            data: 'reporter_id',
            name: 'reporter_id'
        },

        {
            data: 'reporter_login',
            name: 'reporter_login'
        },

        {
            data: 'report_type',
            name: 'report_type'
        },

        {
            data: 'reason',
            name: 'reason'
        },

        {
            data: 'screenshot',
            name: 'screenshot',
            orderable: false,
            searchable: false
        },

        {
            data: 'created_at',
            name: 'created_at'
        },

        {
            data: 'status',
            name: 'status'
        },

        {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false
        }
    ]
    });

/*
* Change report status
*/
$(document).on('change', '.change-status', function () {

    let id = $(this).data('id');
    let status = $(this).val();

    $.ajax({

        url: "{{ route('admin.report.change-status') }}",

        type: "POST",

        data: {
            _token: "{{ csrf_token() }}",
            id: id,
            status: status
        },

        success: function(response) {

            /*
            * Show success message
            */
            toastr.success(response.message);

            /*
            * Reload datatable
            */
            $('#report-data-table').DataTable().ajax.reload(null, false);
        },

        error: function(xhr) {

            /*
            * Validation error
            */
            if (xhr.status === 422) {

                let errors = xhr.responseJSON.errors;

                $.each(errors, function(key, value) {
                    toastr.error(value[0]);
                });

                return;
            }

            /*
            * General error
            */
            toastr.error('Something went wrong');
        }
    });
});

    /*
    * Show group report modal
    */
    $(document).on('click', '.view-group-report-btn', function () {

        let detail = $(this).data('detail') || {};

        $('#modal-report-id').text(detail.report_id || 'N/A');
        $('#modal-group-name').text(detail.group_name || 'N/A');
        $('#modal-group-id').text(detail.group_id || 'N/A');
        $('#modal-group-owner').text(detail.group_owner || 'N/A');
        $('#modal-members-count').text(detail.members_count || '0');
        $('#modal-group-type').text(detail.group_type || 'N/A');
        $('#modal-group-description').text(detail.group_description || 'N/A');

        $('#modal-reporter-name').text(detail.reporter_name || 'N/A');
        $('#modal-reporter-id').text(detail.reporter_id || 'N/A');
        $('#modal-reporter-login').text(detail.reporter_login || 'N/A');

        $('#modal-report-type').text(detail.report_type || 'N/A');
        $('#modal-reason').text(detail.reason || 'N/A');
        $('#modal-created').text(detail.created || 'N/A');

        let image = detail.image || '';

        if (image) {

            $('#modal-image').html(`
                <a href="${image}" data-lightbox="group-report-image">
                    <img src="${image}"
                        width="120"
                        style="border-radius:10px;object-fit:cover;">
                </a>
            `);

        } else {

            $('#modal-image').html('N/A');
        }

        let groupImage = detail.group_image || '';

        if (groupImage) {

            $('#modal-group-image').html(`
                <a href="${groupImage}" data-lightbox="group-detail-image">
                    <img src="${groupImage}"
                        width="120"
                        style="border-radius:10px;object-fit:cover;">
                </a>
            `);

        } else {

            $('#modal-group-image').html('N/A');
        }

        $('#groupReportDetailModal').modal('show');
    });

    /*
    * Delete report
    */
    $(document).on('click', '.delete-report-btn', function () {

        let button = $(this);

        let id = button.data('id');

        // correct
        let groupId = button.data('group-id');

        console.log('Report ID:', id);
        console.log('Group ID:', groupId);

        /*
        * SweetAlert confirmation
        */
        Swal.fire({

            title: 'Are you sure?',
            text: "This group will also be permanently deleted from the app and you won't be able to revert this!",
            icon: 'warning',

            showCancelButton: true,

            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'

        }).then((result) => {

            /*
            * Cancel delete
            */
            if (!result.isConfirmed) {
                return;
            }

            /*
            * Disable button
            */
            button.prop('disabled', true)
                .html('Deleting...');

            $.ajax({

                url: "{{ route('admin.report.delete') }}",

                type: "POST",

                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    type: 'group',
                    groupId: groupId,
                },

                success: function(response) {

                    /*
                    * Success toaster
                    */
                    toastr.success(response.message);

                    /*
                    * Reload datatable
                    */
                    $('#report-data-table')
                        .DataTable()
                        .ajax.reload(null, false);
                },

                error: function(xhr) {

                    /*
                    * Enable button again
                    */
                    button.prop('disabled', false)
                        .html('Delete');

                    /*
                    * Error toaster
                    */
                    toastr.error(
                        xhr.responseJSON?.message
                        ?? 'Something went wrong'
                    );
                }
            });
        });
    });

    /*
    * Auto filter
    */
    let typingTimer;
    const doneTypingInterval = 500;

    $('.filter-input').on('keyup change', function () {

        clearTimeout(typingTimer);

        typingTimer = setTimeout(function () {
            table.ajax.reload();
        }, doneTypingInterval);
    });
    /*
    * Reset filters
    */
    $('#reset-filters').on('click', function () {

        $('.filter-input').val('');

        table.ajax.reload();
    });

</script>

@endsection
