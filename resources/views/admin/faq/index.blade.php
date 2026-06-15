@extends('layouts.admin')



@section('title', 'FAQ List')



@section('content')
<style>
    .message-content {
    white-space: normal !important;
    word-break: break-word;
    overflow-wrap: anywhere;
    line-height: 1.4;
}

#answerModal .modal-dialog {
    max-width: 760px;
    margin: 1.75rem auto;
}

#answerModal .modal-content {
    border-radius: 8px;
    border: none;
}

#answerModal .modal-header {
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
    padding: 15px 20px;
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
</style>
<div class="content-wrapper px-0">

    <div class="row">

        <div class="col-lg-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <h4 class="card-title">FAQ List</h4>

                        <a href="{{ route('admin.faq.create') }}" class="btn btn-primary">Add New FAQ</a>

                    </div>

                    <table class="table table-responsive" id="faq-data-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Question</th>

                                <th>Answer</th>

                                <th>Created At</th>

                                <th>Action</th>

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

<div class="modal fade" id="answerModal" tabindex="-1" role="dialog" aria-labelledby="answerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content message-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="answerModalLabel">Answer Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="fullAnswerText" class="full-message-box"></div>
            </div>
        </div>
    </div>
</div>

@endsection



@section('scripts')

<script>

    let table = $('#faq-data-table').DataTable({

        processing: true,

        serverSide: true,

        ajax: {

            url: "{{ route('admin.faq-list') }}",

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

                data: 'question',

                name: 'question'

            },

            {

                data: 'answer',

                name: 'answer'

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



    // Handle read more/read less toggle

    $(document).on('click', '.toggle-text', function(e) {

        e.preventDefault();

        

        let button = $(this);

        let row = button.closest('td');

        let shortText = row.find('.short-text');

        let fullText = row.find('.full-text');

        

        if (shortText.is(':visible')) {

            shortText.hide();

            fullText.show();

            button.text('Read Less');

        } else {

            fullText.hide();

            shortText.show();

            button.text('Read More');

        }

    });



    // Handle delete

    $(document).on('click', '.delete-faq', function() {

        let button = $(this);

        let faqId = $(this).data('id');

        

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

                // Disable the button and show processing state

                button.prop('disabled', true);

                button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');

                

                $.get("{{ route('refresh.csrf') }}", function(data) {

                    let updatedToken = data.token;

                    let deleteUrl = "{{ route('admin.faq.destroy', ':id') }}".replace(':id', faqId);

                    

                    $.ajax({

                        url: deleteUrl,

                        type: "DELETE",

                        data: {

                            _token: updatedToken

                        },

                        success: function(response) {

                            if (response.success || (response.status === 1)) {

                                Swal.fire({

                                    icon: 'success',

                                    title: 'Deleted!',

                                    text: response.message,

                                    timer: 2000,

                                    showConfirmButton: false

                                });



                                $('#faq-data-table').DataTable().ajax.reload(null, false);

                            } else {

                                Swal.fire('Error!', response.message, 'error');

                            }

                        },

                        error: function(xhr) {

                            Swal.fire('Error!', 'Something went wrong. Try again.', 'error');

                        },

                        complete: function() {

                            // Re-enable button and restore original text

                            button.prop('disabled', false);

                            button.html('Delete');

                        }

                    });

                });

            }

        });

    });

    $(document).on('click', '.view-answer-btn', function () {
        let answer = $(this).attr('data-answer');

        $('#fullAnswerText').text(answer);
        $('#answerModal').modal('show');
    });

</script>

@endsection

