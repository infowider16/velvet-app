@extends('layouts.admin')

@section('title', 'Pin Reports')

@section('content')

<div class="content-wrapper px-0">


<div class="content-header">
    <div class="container-fluid">

        <h1 class="m-0 text-info mb-3">
            <i class="fas fa-flag mr-2"></i>Pin Reports
        </h1>

    </div>
</div>

<section class="content">

    <div class="container-fluid">

        <div class="card shadow-lg border-0" style="border-radius: 10px;">

            <div class="card-header bg-gradient-info text-white" style="border-radius: 10px 10px 0 0;">

                <h3 class="card-title mb-0">
                    <i class="fas fa-list mr-2"></i>Reported Pins
                </h3>

            </div>

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

                            <!-- Reported Pin -->
                            <div class="col-md-4 mb-3">
                                <label>Reported Pin</label>

                                <input type="text"
                                    class="form-control filter-input"
                                    id="filter-pin"
                                    placeholder="Pin ID or Preview">
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

                    <thead class="bg-light">
                       <tr>
                            <th>#</th>
                            <th>Pin ID</th>
                            <th>Pin Preview</th>
                            <th>Pin Author</th>
                            <th>Author ID</th>
                            <th>Reporter Name</th>
                            <th>Reporter ID</th>
                            <th>Reporter Login</th>
                            <th>Report Type</th>
                            <th>Reason</th>
                            <th>Screenshot</th>
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

<!-- Report Detail Modal -->

<div class="modal fade" id="reportDetailModal" tabindex="-1">

<div class="modal-dialog modal-lg" style="max-width: 700px;">

    <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">

        <div class="modal-header bg-gradient-info text-white" style="border-bottom: none; border-radius: 12px 12px 0 0;">

            <h5 class="modal-title font-weight-bold">
                <i class="fas fa-flag mr-2"></i>Pin Report Details
            </h5>

            <button type="button"
                    class="close text-white"
                    data-dismiss="modal"
                    style="opacity: 1;">

                <span>&times;</span>

            </button>

        </div>

        <div class="modal-body" style="padding: 2rem;">

            <!-- Pin Information Section -->
            <div class="mb-4">
                <h6 class="text-uppercase font-weight-bold text-info mb-3" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                    <i class="fas fa-image mr-2"></i>Pin Information
                </h6>
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label class="font-weight-bold text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Pin ID</label>
                        <p id="modal-pin-id" class="text-dark mb-0" style="font-size: 1.1rem;"></p>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label class="font-weight-bold text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Pin Author</label>
                        <p id="modal-pin-author" class="text-dark mb-0" style="font-size: 1.1rem;"></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label class="font-weight-bold text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Pin Author ID</label>
                        <p id="modal-pin-author-id" class="text-dark mb-0" style="font-size: 1rem;"></p>
                    </div>
                </div>
            </div>

            <hr style="background-color: #e9ecef; margin: 1.5rem 0;">

            <!-- Report Information Section -->
            <div class="mb-4">
                <h6 class="text-uppercase font-weight-bold text-info mb-3" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                    <i class="fas fa-report mr-2"></i>Report Information
                </h6>
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label class="font-weight-bold text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Report Type</label>
                        <p id="modal-report-type" class="text-dark mb-0" style="font-size: 1.1rem;"></p>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label class="font-weight-bold text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Reported At</label>
                        <p id="modal-created" class="text-dark mb-0" style="font-size: 1rem;"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="font-weight-bold text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Reason</label>
                        <p id="modal-reason" class="text-dark mb-0 p-2" style="background-color: #f8f9fa; border-radius: 5px; font-size: 1rem; border-left: 3px solid #17a2b8;"></p>
                    </div>
                </div>
            </div>

            <hr style="background-color: #e9ecef; margin: 1.5rem 0;">

            <!-- Reporter Information Section -->
            <div class="mb-4">
                <h6 class="text-uppercase font-weight-bold text-info mb-3" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                    <i class="fas fa-user mr-2"></i>Reporter Information
                </h6>
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label class="font-weight-bold text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Reporter Name</label>
                        <p id="modal-reporter-name" class="text-dark mb-0" style="font-size: 1.1rem;"></p>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label class="font-weight-bold text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Reporter ID</label>
                        <p id="modal-reporter-id" class="text-dark mb-0" style="font-size: 1.1rem;"></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label class="font-weight-bold text-muted" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Reporter Login</label>
                        <p id="modal-reporter-login" class="text-dark mb-0" style="font-size: 1.1rem;"></p>
                    </div>
                </div>
            </div>

            <hr style="background-color: #e9ecef; margin: 1.5rem 0;">

            <!-- Pin Preview Section -->
            <div class="mb-4">
                <h6 class="text-uppercase font-weight-bold text-info mb-3" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                    <i class="fas fa-eye mr-2"></i>Pin Content Preview
                </h6>
                <div class="p-2" style="background-color: #f8f9fa; border-radius: 5px; border-left: 3px solid #17a2b8;">
                    <p id="modal-pin-preview" class="text-dark mb-0" style="font-size: 0.95rem; line-height: 1.5;"></p>
                </div>
            </div>

            <!-- Screenshot Section -->
            <div class="mb-4">
                <h6 class="text-uppercase font-weight-bold text-info mb-3" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                    <i class="fas fa-image mr-2"></i>Screenshot Evidence
                </h6>
                <div id="modal-image" class="text-center"></div>
            </div>

        </div>

    </div>

</div>


</div>

@endsection

@section('scripts')

<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css"
      rel="stylesheet">


<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

<script>

    /*
     * Initialize Pin Report DataTable
     */
    let table = $('#report-data-table').DataTable({

        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,
        paging: true,
        info: true,

        ajax: {

            url: "{{ route('admin.pin-reports.list') }}",
            data: function (d) {
                d.status   = $('#filter-status').val();
                d.date     = $('#filter-date').val();
                d.reason   = $('#filter-reason').val();
                d.reporter = $('#filter-reporter').val();
                d.pin      = $('#filter-pin').val();
            },
            error: function(xhr) {

                /*
                 * Show error message if request fails
                 */
                toastr.error('Something went wrong while loading pin reports');
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
                data: 'pin_id',
                name: 'pin_id'
            },

            {
                data: 'pin_preview',
                name: 'pin_preview'
            },

            {
                data: 'pin_author',
                name: 'pin_author'
            },

            {
                data: 'pin_author_id',
                name: 'pin_author_id'
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
     * Show report detail modal
     */
    $(document).on('click', '.view-report-btn', function () {

        $('#modal-pin-id').text($(this).data('pin-id'));
        $('#modal-pin-preview').text($(this).data('pin-preview'));
        $('#modal-pin-author').text($(this).data('pin-author'));
        $('#modal-pin-author-id').text($(this).data('pin-author-id'));

        $('#modal-reporter-name').text($(this).data('reporter-name'));
        $('#modal-reporter-id').text($(this).data('reporter-id'));
        $('#modal-reporter-login').text($(this).data('reporter-login'));

        $('#modal-report-type').text($(this).data('report-type'));
        $('#modal-reason').text($(this).data('reason'));
        $('#modal-created').text($(this).data('created'));

        /*
         * Show image preview
         */
        let image = $(this).data('image');

        $('#modal-image').html(`
            <a href="${image}" data-lightbox="report-image">
                <img src="${image}"
                     width="120"
                     style="border-radius:10px;object-fit:cover;">
            </a>
        `);

        /*
         * Open modal
         */
        $('#reportDetailModal').modal('show');
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
                $('#report-data-table')
                    .DataTable()
                    .ajax.reload(null, false);
            },

            error: function(xhr) {

                /*
                * Validation errors
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
    * Delete report
    */
    $(document).on('click', '.delete-report-btn', function () {

        let button = $(this);
        let id = button.data('id');

        /*
        * SweetAlert confirmation
        */
        Swal.fire({

            title: 'Are you sure?',
            text: "You won't be able to revert this!",
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
                    id: id
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
    $('#reset-filters').on('click', function () {

        /*
        * Clear all filter fields
        */
        $('.filter-input').val('');

        /*
        * Reload datatable
        */
        $('#report-data-table')
            .DataTable()
            .ajax.reload();
    });

</script>

@endsection
