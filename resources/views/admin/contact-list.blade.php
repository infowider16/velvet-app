@extends('layouts.admin')



@section('title', 'Contact List')



@section('content')



<div class="content-wrapper">

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

    $(document).on('click', '.toggle-text', function(e) {

        e.preventDefault();

        

        let button = $(this);

        let row = button.closest('td');

        let shortText = row.find('.short-text');

        let fullText = row.find('.full-text');

        

        if (shortText.is(':visible')) {

            // Show full text

            shortText.hide();

            fullText.show();

            button.text('Read Less');

        } else {

            // Show short text

            fullText.hide();

            shortText.show();

            button.text('Read More');

        }

    });



    // Handle delete user button click

  

</script>

@endsection



