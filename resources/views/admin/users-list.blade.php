@extends('layouts.admin')

@section('title', 'User Management')
@push('styles')
<style>
    .content-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }
    
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    
    .card-header {
        border-radius: 15px 15px 0 0 !important;
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }
    
    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        margin: 0 2px;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .modal-content {
        border-radius: 15px;
    }
    
    .form-control {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
        transform: scale(1.02);
    }
    
    .badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }
    
    .alert {
        border-radius: 10px;
    }
    
    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-left: 4px solid #ffc107;
    }
    
    /* Status badges */
    .badge-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    
    /* Action buttons grouping */
    .action-buttons {
        white-space: nowrap;
    }
    
    .action-buttons .btn {
        margin: 0 1px;
        min-width: 80px;
    }
    
    /* DataTable custom styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
    
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 1rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px !important;
        margin: 0 2px;
    }
</style>
@endpush
@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-info">
                        <i class="fas fa-users mr-2"></i>User Management
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-gradient-info text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-list mr-2"></i>Registered Users
                                </h3>
                                
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0" id="user-data-table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0">#</th>
                                            <th class="border-0">
                                                <i class="fas fa-user mr-1"></i>Name
                                            </th>
                                            <th class="border-0">
                                                <i class="fab fa-google mr-1"></i>Google Email
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-flag mr-1"></i>Country Code
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-phone mr-1"></i>Phone
                                            </th>
                                            <th class="border-0 text-center">
                                                <i class="fas fa-toggle-on mr-1"></i>Status
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-calendar mr-1"></i>Created At
                                            </th>
                                            <th class="border-0 text-center">
                                                <i class="fas fa-cogs mr-1"></i>Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modern Change Phone Modal -->
<div class="modal fade" id="changePhoneModal" tabindex="-1" role="dialog" aria-labelledby="changePhoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-warning text-white border-0">
                <h5 class="modal-title" id="changePhoneModalLabel">
                    <i class="fas fa-phone-alt mr-2"></i>Change Phone Number
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="changePhoneForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="userId" name="user_id">
                    
                    <div class="alert alert-warning border-0 shadow-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Warning:</strong> Changing the phone number will affect user's login credentials.
                    </div>
                    
                    <div class="form-group">
                        <label for="phoneNumber" class="font-weight-bold">
                            <i class="fas fa-phone mr-1 text-warning"></i>New Phone Number 
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg" id="phoneNumber" name="phone_number" 
                               placeholder="Enter new phone number" required>
                        <small class="form-text text-muted">Enter the complete phone number with country code</small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i>Update Phone
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>

    

    let table = $('#user-data-table').DataTable({

        processing: true,

        serverSide: true,

        ajax: {

            url: "{{ route('admin.user-list') }}",

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

                data: 'username',

                name: 'username'

            },

            {

                data: 'google_id',

                name: 'google_id'

            },

            {

                data: 'country_code',

                name: 'country_code'

            },

            {

                data: 'phone',

                name: 'phone'

            },

            {

                data: 'status',

                name: 'status',

                orderable: false,

                searchable: false

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

            },

        ]

    });
    
    $(document).on('click', '.delete-user', function(e) {
        console.log('Delete button clicked');
        e.preventDefault();



        let userId = $(this).data('id');

        let button = $(this);



        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {

            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });



            $.ajax({

                url: "{{ route('admin.user.destroy', ':id') }}".replace(':id', userId),

                type: 'DELETE',

                beforeSend: function() {

                    button.prop('disabled', true).text('Deleting...');

                },

                success: function(response) {

                    if (response.status == 1) {

                        toastr.success(response.message);

                        table.ajax.reload();

                    } else {

                        toastr.error(response.message);

                    }

                },

                error: function(xhr) {

                    let message = 'Something went wrong while deleting user';

                    if (xhr.responseJSON && xhr.responseJSON.message) {

                        message = xhr.responseJSON.message;

                    }

                    toastr.error(message);

                },

                complete: function() {

                    button.prop('disabled', false).text('Delete');

                }

            });

        }

    });
   

    $(document).on('click', '.toggle-status', function() {

        let button = $(this);

        let userId = $(this).data('id');

        let status = $(this).data('status');

        button.prop('disabled', true);

        $.get("{{ route('refresh.csrf') }}", function(data) {

            let updatedToken = data.token;

            $.ajax({

                url: "{{ route('admin.user.toggleStatus') }}",

                type: "POST",

                data: {

                    _token: updatedToken,

                    user_id: userId,

                    status: status

                },

                success: function(response) {

                    Swal.fire({

                        icon: 'success',

                        title: 'Success',

                        text: response.message,

                        timer: 2000,

                        showConfirmButton: false

                    });



                    $('#user-data-table').DataTable().ajax.reload(null, false);

                },

                error: function(xhr) {

                    if (xhr.status === 419) {

                        Swal.fire({

                            icon: 'error',

                            title: 'Session Expired',

                            text: 'Your session has expired. Please refresh the page.',

                        });

                    } else {

                        Swal.fire('Error!', 'Something went wrong. Try again.', 'error');

                    }

                },

                complete: function() {

                    // re-enable button after request is done (success or error)

                    button.prop('disabled', false);

                }

            });

        });

    });

    // Handle change number button click
    $(document).on('click', '.change-number', function(e) {
        e.preventDefault();
        
        let userId = $(this).data('id');
        // let phoneCode = $(this).data('phone-code');
        let phoneNumber = $(this).data('phone-number');
        // let countryCode = $(this).data('country-code');
        
        // Populate modal fields
        $('#userId').val(userId);
        // $('#phoneCode').val(phoneCode);
        $('#phoneNumber').val(phoneNumber);
        // $('#countryCode').val(countryCode);
        
        // Show modal
        $('#changePhoneModal').modal('show');
    });

    // Handle form submission
    $('#changePhoneForm').on('submit', function(e) {
        e.preventDefault();
        
        let userId = $('#userId').val();
        let formData = {
            // phone_code: $('#phoneCode').val(),
            phone_number: $('#phoneNumber').val(),
            // country_code: $('#countryCode').val(),
            _method: 'PUT'
        };
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        $.ajax({
            url: "{{ route('admin.user.updatePhone', ':id') }}".replace(':id', userId),
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#changePhoneForm button[type="submit"]').prop('disabled', true).text('Updating...');
            },
            success: function(response) {
                if (response.status == 1) {
                    toastr.success(response.message);
                    $('#changePhoneModal').modal('hide');
                    table.ajax.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                let message = 'Something went wrong while updating phone number';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            },
            complete: function() {
                $('#changePhoneForm button[type="submit"]').prop('disabled', false).text('Update Phone');
            }
        });
    });

</script>
@endsection

