@extends('layouts.admin')



@section('title', 'Contact List')



@section('content')

<style>
    #contact-data-table {
        width: 100% !important;
    }

    #contact-data-table th,
    #contact-data-table td {
        vertical-align: top;
        white-space: normal !important;
    }

    .message-content {
        white-space: normal !important;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.4;
    }

    #messageModal .modal-dialog {
        max-width: 760px;
        margin: 1.75rem auto;
    }

    #messageModal .modal-content {
        border-radius: 8px;
        border: none;
    }

    #messageModal .modal-header {
        background: #f8f9fa;
        border-bottom: 1px solid #ddd;
        padding: 15px 20px;
    }

    #messageModal .modal-title {
        font-size: 18px;
        font-weight: 600;
    }

    #messageModal .modal-body {
        padding: 20px;
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
    .card-body {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

#contact-data-table {
    width: 100% !important;
    min-width: 1000px;
}

#contact-data-table th,
#contact-data-table td {
    white-space: nowrap !important;
}
</style>

<div class="content-wrapper px-0">

    <div class="row">

        <div class="col-lg-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <h4 class="card-title">Contact Us List</h4>

                    <table class="table" id="contact-data-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Name</th>

                                <th>Email</th>

                                <th>Image</th>

                                <th>Subject</th>

                                <th>Message</th>

                                <th>Created At</th>

                             

                            </tr>

                        </thead>

                        <tbody>

                            {{-- DataTables will populate --}}

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content message-modal-content">
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

<script>

    let table = $('#contact-data-table').DataTable({

        processing: true,

        serverSide: true,

        ajax: {

            url: "{{ route('admin.contact-list') }}",

            data: function(d) {

                // No filters for now

            }

        },

        columns: [{

                data: 'DT_RowIndex',

                name: 'DT_RowIndex',

                orderable: false,

                searchable: false

            },

            {

                data: 'name',

                name: 'name'

            },

            {

                data: 'email',

                name: 'email'

            },

            {

                data: 'image',

                name: 'image',

                orderable: false,

                searchable: false

            },

            {

                data: 'subject',

                name: 'subject'

            },

            {

                data: 'message',

                name: 'message'

            },

          

            {

                data: 'created_at',

                name: 'created_at'

            }

        ]

    });



    // Handle read more/read less toggle
    $(document).on('click', '.view-message-btn', function () {
        let message = $(this).attr('data-message');

        $('#fullMessageText').text(message);
        $('#messageModal').modal('show');
    });

  

</script>

@endsection



