@extends('layouts.admin')

@section('title', 'User Reports')

@section('content')

<div class="content-wrapper px-0">

    <div class="content-header">
        <div class="container-fluid">

            <h1 class="m-0 text-info mb-3">
                <i class="fas fa-user-shield mr-2"></i>User Reports
            </h1>

        </div>
    </div>

    <section class="content">

        <div class="container-fluid">

            <div class="card shadow-lg border-0">

                <div class="card-header bg-gradient-info text-white">

                    <h3 class="card-title mb-0">
                        <i class="fas fa-list mr-2"></i>Reported Users
                    </h3>

                </div>

                <div class="card-body">
                    <div class="card-body p-0">

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

                                    <!-- Report Reason -->
                                    <div class="col-md-3 mb-3">
                                        <label>Report Reason</label>

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

                                    <!-- Reported User -->
                                    <div class="col-md-4 mb-3">
                                        <label>Reported User</label>

                                        <input type="text"
                                            class="form-control filter-input"
                                            id="filter-user"
                                            placeholder="User name or ID">
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
                                    <th>Reported User</th>
                                    <th>Reported User ID</th>
                                    <th>Reported User Login</th>
                                    <th>Reporter Name</th>
                                    <th>Reporter ID</th>
                                    <th>Reporter Login</th>
                                    <th>Report Type</th>
                                    <th>Report Reason</th>
                                    <th>Image</th>
                                    <th>Date & Time</th>
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
            url: "{{ route('admin.user-reports.list') }}",
            data: function(d) {

                d.status = $('#filter-status').val();
                d.date = $('#filter-date').val();
                d.reason = $('#filter-reason').val();
                d.reporter = $('#filter-reporter').val();
                d.user = $('#filter-user').val();
            },
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
                data: 'reported_user_name',
                name: 'reported_user_name'
            },

            {
                data: 'reported_user_id',
                name: 'reported_user_id'
            },

            {
                data: 'reported_user_login',
                name: 'reported_user_login'
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
                name: 'status',
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
    $(document).on('change', '.change-status', function() {

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
     * Apply filters automatically
     */
    let filterTimer;
    let isReloading = false;

    $(document).on('keyup change', '.filter-input', function () {

        clearTimeout(filterTimer);

        filterTimer = setTimeout(function () {

            if (isReloading) return;

            isReloading = true;

            $('#report-data-table')
                .DataTable()
                .ajax.reload(function () {
                    isReloading = false;
                }, false);

        }, 500);
    });

    /*
     * Reset filters
     */
    $('#reset-filters').on('click', function() {

        /*
         * Clear all filters
         */
        $('.filter-input').val('');

        /*
         * Reload table
         */
        $('#report-data-table')
            .DataTable()
            .ajax.reload();
    });
</script>

@endsection