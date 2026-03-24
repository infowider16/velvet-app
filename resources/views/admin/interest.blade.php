@extends('layouts.admin')

@section('title', 'Interest Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Interest Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Interests</li>
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
                            <h3 class="card-title">Interest List</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" id="add-interest">
                                    <i class="fas fa-plus"></i> Add Interest
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="interest-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Sr No</th>
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

<!-- Interest Modal -->
<div class="modal fade" id="interestModal" tabindex="-1" role="dialog" aria-labelledby="interestModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="interestModalLabel">Add Interest</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="interestForm">
                <div class="modal-body">
                    <input type="hidden" id="interest-id" name="id">

                    <div class="form-group">
                        <label for="name_en">Name (English) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name_en" name="name_translation[en]" placeholder="Enter interest name in English" maxlength="255" required>
                        <div class="invalid-feedback" id="error-name_en"></div>
                    </div>

                    <div class="form-group">
                        <label for="name_ge">Name (German) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name_ge" name="name_translation[ge]" placeholder="Enter interest name in German" maxlength="255" required>
                        <div class="invalid-feedback" id="error-name_ge"></div>
                    </div>

                    <div class="form-group">
                        <label for="parent_id">Parent Interest</label>
                        <select class="form-control" id="parent_id" name="parent_id">
                            <option value="0">Main Interest</option>
                            @foreach(($parentInterests ?? []) as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="error-parent_id"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="save-interest">Save</button>
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
        $('#interestForm .form-control').removeClass('is-invalid');
        $('#interestForm .invalid-feedback').text('');
    }

    function setInvalid(id, message) {
        $('#' + id).addClass('is-invalid');
        $('#error-' + id).text(message);
    }

    function resetForm() {
        $('#interestForm')[0].reset();
        $('#interest-id').val('');
        $('#parent_id').val('0');
        clearValidation();
    }

    var table = $('#interest-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.interest-list') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        responsive: true,
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
        }
    });

    $('#add-interest').click(function() {
        resetForm();
        $('#interestModal').modal('show');
        $('#interestModalLabel').text('Add Interest');
        $('#save-interest').text('Save');
    });

    $(document).on('click', '.edit-interest', function() {
        resetForm();

        $('#interestModal').modal('show');
        $('#interestModalLabel').text('Edit Interest');
        $('#save-interest').text('Update');

        $('#interest-id').val($(this).data('id'));
        $('#name_en').val($(this).data('name-en'));
        $('#name_ge').val($(this).data('name-ge'));
        $('#parent_id').val($(this).data('parent-id') || 0);
    });

    $('#interestForm').submit(function(e) {
        e.preventDefault();
        clearValidation();

        var id = $('#interest-id').val();
        var nameEn = $('#name_en').val().trim();
        var nameGe = $('#name_ge').val().trim();
        var parentId = $('#parent_id').val();

        var hasError = false;

        if (!nameEn) {
            setInvalid('name_en', 'English name is required.');
            hasError = true;
        } else if (nameEn.length > 255) {
            setInvalid('name_en', 'English name must be at most 255 characters.');
            hasError = true;
        }

        if (!nameGe) {
            setInvalid('name_ge', 'German name is required.');
            hasError = true;
        } else if (nameGe.length > 255) {
            setInvalid('name_ge', 'German name must be at most 255 characters.');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        var formData = new FormData(this);
        var url = id ? "{{ url('admin/interests') }}/" + id : "{{ route('admin.interest.store') }}";

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
                $('#save-interest').prop('disabled', true).text('Saving...');
            },
            success: function(response) {
                if (response.status === 1 || response.status === true) {
                    $('#interestModal').modal('hide');
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
                            'name_translation.en': 'name_en',
                            'name_translation.ge': 'name_ge',
                            'parent_id': 'parent_id'
                        };

                        if (fieldMap[key]) {
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
                $('#save-interest').prop('disabled', false).text(id ? 'Update' : 'Save');
            }
        });
    });

    $(document).on('click', '.delete-interest', function() {
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
                    url: "{{ url('admin/interests') }}/" + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status === 1 || response.status === true) {
                            table.ajax.reload();

                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message || 'Interest deleted successfully.',
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

    $('#interestModal').on('hidden.bs.modal', function() {
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