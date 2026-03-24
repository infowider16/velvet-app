@extends('layouts.admin')

@section('title', 'Sub Interest Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Sub Interest Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Sub Interests</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Sub Interest List</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" id="add-sub-interest">
                                    <i class="fas fa-plus"></i> Add Sub Interest
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="sub-interest-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Sr No</th>
                                        <th>Parent Interest</th>
                                        <th>Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Sub Interest Modal -->
<div class="modal fade" id="subInterestModal" tabindex="-1" role="dialog" aria-labelledby="subInterestModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subInterestModalLabel">Add Sub Interest</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="subInterestForm">
                <div class="modal-body">
                    <input type="hidden" id="sub-interest-id" name="id">

                    <div class="form-group">
                        <label for="parent-interest">Parent Interest <span class="text-danger">*</span></label>
                        <select class="form-control" id="parent-interest" name="parent_id" required>
                            <option value="">Select Parent Interest</option>
                        </select>
                        <div class="invalid-feedback" id="error-parent-interest"></div>
                    </div>

                    <div class="form-group">
                        <label for="sub_name_en">Sub Interest Name (English) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sub_name_en" name="name_translation[en]" placeholder="Enter sub interest name in English" maxlength="255" required>
                        <div class="invalid-feedback" id="error-sub_name_en"></div>
                    </div>

                    <div class="form-group">
                        <label for="sub_name_ge">Sub Interest Name (German) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sub_name_ge" name="name_translation[ge]" placeholder="Enter sub interest name in German" maxlength="255" required>
                        <div class="invalid-feedback" id="error-sub_name_ge"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="save-sub-interest">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function extractXhrMessage(xhr, fallback) {
        var msg = fallback || 'Something went wrong. Please try again.';
        try {
            if (xhr && xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) {
                return xhr.responseJSON.message || xhr.responseJSON.error;
            }
            if (xhr && xhr.responseText) {
                var json = JSON.parse(xhr.responseText);
                if (json && (json.message || json.error)) {
                    return json.message || json.error;
                }
                return xhr.responseText;
            }
        } catch (e) {}
        return msg;
    }

    function clearValidation() {
        $('#subInterestForm .form-control').removeClass('is-invalid');
        $('#subInterestForm .invalid-feedback').text('');
    }

    function setInvalid(id, message) {
        $('#' + id).addClass('is-invalid');
        $('#error-' + id).text(message);
    }

    var table = $('#sub-interest-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.sub-interest-list') }}",
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to load data. Please refresh the page.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'parent_name', name: 'parent_name'},
            {data: 'name', name: 'name'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        responsive: true,
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
        }
    });

    function loadParentInterests(selectedId = '') {
        $.ajax({
            url: "{{ route('admin.interest.parents') }}",
            method: 'GET',
            beforeSend: function() {
                $('#parent-interest').html('<option value="">Loading...</option>');
            },
            success: function(response) {
                var options = '<option value="">Select Parent Interest</option>';

                if (response.status && response.data && response.data.length > 0) {
                    $.each(response.data, function(index, interest) {
                        options += '<option value="' + interest.id + '">' + interest.name + '</option>';
                    });
                } else {
                    options += '<option value="" disabled>No parent interests available</option>';
                }

                $('#parent-interest').html(options);

                if (selectedId) {
                    $('#parent-interest').val(selectedId);
                }
            },
            error: function() {
                $('#parent-interest').html('<option value="">Error loading options</option>');
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to load parent interests. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
            }
        });
    }

    function resetForm() {
        $('#subInterestForm')[0].reset();
        $('#sub-interest-id').val('');
        clearValidation();
        $('#parent-interest').html('<option value="">Select Parent Interest</option>');
    }

    $('#add-sub-interest').click(function() {
        resetForm();
        $('#subInterestModal').modal('show');
        $('#subInterestModalLabel').text('Add Sub Interest');
        $('#save-sub-interest').text('Save');
        loadParentInterests();
    });

    $(document).on('click', '.edit-sub-interest', function() {
        var id = $(this).data('id');
        var nameEn = $(this).data('name-en');
        var nameGe = $(this).data('name-ge');
        var parentId = $(this).data('parent-id');

        resetForm();
        $('#subInterestModal').modal('show');
        $('#subInterestModalLabel').text('Edit Sub Interest');
        $('#save-sub-interest').text('Update');

        $('#sub-interest-id').val(id);
        $('#sub_name_en').val(nameEn);
        $('#sub_name_ge').val(nameGe);

        loadParentInterests(parentId);
    });

    $('#subInterestForm').submit(function(e) {
        e.preventDefault();
        clearValidation();

        var id = $('#sub-interest-id').val();
        var parentId = $('#parent-interest').val();
        var nameEn = $('#sub_name_en').val().trim();
        var nameGe = $('#sub_name_ge').val().trim();

        var hasError = false;

        if (!parentId) {
            $('#parent-interest').addClass('is-invalid');
            $('#error-parent-interest').text('Parent interest is required.');
            hasError = true;
        }

        if (!nameEn) {
            setInvalid('sub_name_en', 'English name is required.');
            hasError = true;
        } else if (nameEn.length > 255) {
            setInvalid('sub_name_en', 'English name must be at most 255 characters.');
            hasError = true;
        }

        if (!nameGe) {
            setInvalid('sub_name_ge', 'German name is required.');
            hasError = true;
        } else if (nameGe.length > 255) {
            setInvalid('sub_name_ge', 'German name must be at most 255 characters.');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        var formData = new FormData(this);
        var url = id ? "{{ url('admin/sub-interests') }}/" + id : "{{ route('admin.sub-interest.store') }}";

        if (id) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#save-sub-interest').prop('disabled', true).text('Saving...');
            },
            success: function(response) {
                if (response.status === 1 || response.status === true) {
                    $('#subInterestModal').modal('hide');
                    table.ajax.reload();

                    Swal.fire({
                        title: 'Success!',
                        text: response.message || 'Saved successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;

                    $.each(errors, function(key, value) {
                        var fieldMap = {
                            'parent_id': 'parent-interest',
                            'name_translation.en': 'sub_name_en',
                            'name_translation.ge': 'sub_name_ge'
                        };

                        if (key === 'parent_id') {
                            $('#parent-interest').addClass('is-invalid');
                            $('#error-parent-interest').text(value[0]);
                        } else if (fieldMap[key]) {
                            setInvalid(fieldMap[key], value[0]);
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: extractXhrMessage(xhr, 'Something went wrong. Please try again.'),
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                }
            },
            complete: function() {
                $('#save-sub-interest').prop('disabled', false).text(id ? 'Update' : 'Save');
            }
        });
    });

    $(document).on('click', '.delete-sub-interest', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/sub-interests') }}/" + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status === 1 || response.status === true) {
                            table.ajax.reload();

                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message || 'Sub Interest deleted successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: extractXhrMessage(xhr, 'Something went wrong. Please try again.'),
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            }
        });
    });

    $('#subInterestModal').on('hidden.bs.modal', function() {
        resetForm();
    });
});
</script>
@endpush

@push('styles')
<style>
    .invalid-feedback {
        display: block;
    }

    .swal2-popup {
        font-size: 1rem;
    }

    .btn-sm {
        margin-right: 5px;
    }

    .card-tools .btn {
        margin-left: 5px;
    }
</style>
@endpush