@extends('layouts.admin')



@section('title', 'FAQ List')



@section('content')

<div class="content-wrapper">

    <div class="row">

        <div class="col-lg-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <h4 class="card-title">FAQ List</h4>

                        <a href="{{ route('admin.faq.create') }}" class="btn btn-primary">Add New FAQ</a>

                    </div>

                    <table class="table" id="faq-data-table">

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

</script>

@endsection

