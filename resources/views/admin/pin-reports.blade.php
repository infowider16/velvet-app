@extends('layouts.admin')

@section('title', 'Group Reports')

@section('content')
<div class="content-wrapper px-0">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0 text-info mb-3">
                <i class="fas fa-flag mr-2"></i>Group Reports
            </h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-info text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-list mr-2"></i>Reported Pins
                    </h3>
                </div>

                <div class="card-body">
                    <div class="">
                        <table class="table table-hover table-striped table-responsive" id="report-data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Pin</th>
                                    <th>Reporter Name</th>
                                    <th>Reporter Email</th>
                                    <th>Group</th>
                                    <th>Report Type</th>
                                    <th>Reported At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
<!-- Report Detail Modal -->
<div class="modal fade" id="reportDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">
                    Report Details
                </h5>

                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <table class="table table-bordered">
                    <tr>
                        <th width="200">Pin</th>
                        <td id="modal-pin"></td>
                    </tr>

                    <tr>
                        <th>Reporter Name</th>
                        <td id="modal-reporter"></td>
                    </tr>

                    <tr>
                        <th>Reporter Email</th>
                        <td id="modal-email"></td>
                    </tr>

                    <tr>
                        <th>Group</th>
                        <td id="modal-group"></td>
                    </tr>

                    <tr>
                        <th>Report Type</th>
                        <td id="modal-report-type"></td>
                    </tr>

                    <tr>
                        <th>Reason</th>
                        <td id="modal-reason"></td>
                    </tr>

                    <tr>
                        <th>Image</th>
                        <td id="modal-image"></td>
                    </tr>

                    <tr>
                        <th>Reported At</th>
                        <td id="modal-created"></td>
                    </tr>
                </table>

            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<script>
    /*
     * Initialize report Yajra DataTable
     */
    let table = $('#report-data-table').DataTable({
        processing: true,
        serverSide: true,

        ajax: {
            url: "{{ route('admin.report-list') }}",

            data: function(d) {
                /*
                 * Add filters here later if needed
                 */
            },

            error: function(xhr) {
                /*
                 * Show error message if DataTable request fails
                 */
                toastr.error('Something went wrong while loading reports');
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
                data: 'pin',
                name: 'pin'
            },
            {
                data: 'reporter_name',
                name: 'reporter.name'
            },
            {
                data: 'reporter_email',
                name: 'reporter.gmail_id'
            },
            {
                data: 'group_name',
                name: 'group.name'
            },
            {
                data: 'report_type',
                name: 'report_type'
            },
            {
                data: 'created_at',
                name: 'created_at'
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

        $('#modal-pin').text($(this).data('pin'));
        $('#modal-reporter').text($(this).data('reporter'));
        $('#modal-email').text($(this).data('email'));
        $('#modal-group').text($(this).data('group'));
        $('#modal-report-type').text($(this).data('report-type'));
        $('#modal-reason').text($(this).data('reason'));
        $('#modal-created').text($(this).data('created'));

        /*
        * Show image with lightbox
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
    
</script>
@endsection