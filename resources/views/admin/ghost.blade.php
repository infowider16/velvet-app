@extends('layouts.admin')

@section('title', 'Ghost Plan Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Ghost Plan Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Ghost Plans</li>
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
                            <h3 class="card-title">Ghost Plan List</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" id="add-ghost">
                                    <i class="fas fa-plus"></i> Add Ghost Plan
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="ghost-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Sr No</th>
                                        <th>Tag</th>
                                        <th>Title</th>
                                        <th>Duration</th>
                                        <th>Unit</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plans as $index => $plan)
                                        @php
                                            $durationRaw = $plan->getRawOriginal('duration');
                                            $durationParts = is_string($durationRaw) ? explode('_', $durationRaw) : [];
                                            $durationValue = $durationParts[0] ?? '';
                                            $durationUnit = $durationParts[1] ?? '';

                                            $tagTranslations = $plan->tag_translation ?? [];
                                            $titleTranslations = $plan->title_translation ?? [];
                                            $durationTranslations = $plan->duration_translation ?? [];
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $plan->tag }}</td>
                                            <td>{{ $plan->title }}</td>
                                            <td>{{ $durationValue }}</td>
                                            <td>{{ ucfirst($durationUnit) }}</td>
                                            <td>{{ $plan->amount }}</td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info edit-ghost"
                                                    data-id="{{ $plan->id }}"
                                                    data-tag-en="{{ $tagTranslations['en'] ?? $plan->tag }}"
                                                    data-tag-ge="{{ $tagTranslations['ge'] ?? '' }}"
                                                    data-title-en="{{ $titleTranslations['en'] ?? $plan->title }}"
                                                    data-title-ge="{{ $titleTranslations['ge'] ?? '' }}"
                                                    data-duration-en="{{ $durationTranslations['en'] ?? $durationValue }}"
                                                    data-duration-ge="{{ $durationTranslations['ge'] ?? '' }}"
                                                    data-duration-value="{{ $durationValue }}"
                                                    data-unit="{{ $durationUnit }}"
                                                    data-amount="{{ $plan->amount }}"
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger delete-ghost"
                                                    data-id="{{ $plan->id }}"
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="ghostModal" tabindex="-1" role="dialog" aria-labelledby="ghostModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="ghostModalLabel">Add Ghost Plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="ghostForm">
                <div class="modal-body">
                    <input type="hidden" id="ghost-id" name="id">

                    <div class="form-group">
                        <label for="tag_en">Tag (English) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tag_en" name="tag_translation[en]" maxlength="100" required>
                        <div class="invalid-feedback" id="error-tag_en"></div>
                    </div>

                    <div class="form-group">
                        <label for="tag_ge">Tag (German) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tag_ge" name="tag_translation[ge]" maxlength="100" required>
                        <div class="invalid-feedback" id="error-tag_ge"></div>
                    </div>

                    <div class="form-group">
                        <label for="title_en">Title (English) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title_en" name="title_translation[en]" maxlength="255" required>
                        <div class="invalid-feedback" id="error-title_en"></div>
                    </div>

                    <div class="form-group">
                        <label for="title_ge">Title (German) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title_ge" name="title_translation[ge]" maxlength="255" required>
                        <div class="invalid-feedback" id="error-title_ge"></div>
                    </div>

                    <div class="form-group">
                        <label for="duration_value">Duration Value <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="duration_value" name="duration_value" min="1" max="365" required>
                        <div class="invalid-feedback" id="error-duration_value"></div>
                    </div>

                    <div class="form-group">
                        <label for="unit">Unit <span class="text-danger">*</span></label>
                        <select class="form-control" id="unit" name="unit" required>
                            <option value="">Select unit</option>
                            <option value="days">Days</option>
                            <option value="hours">Hours</option>
                        </select>
                        <div class="invalid-feedback" id="error-unit"></div>
                    </div>

                    <div class="form-group">
                        <label for="duration_en">Duration Text (English) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="duration_en" name="duration_translation[en]" maxlength="100" required>
                        <div class="invalid-feedback" id="error-duration_en"></div>
                    </div>

                    <div class="form-group">
                        <label for="duration_ge">Duration Text (German) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="duration_ge" name="duration_translation[ge]" maxlength="100" required>
                        <div class="invalid-feedback" id="error-duration_ge"></div>
                    </div>

                    <div class="form-group">
                        <label for="amount">Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount" name="amount" min="0" max="99999999.99" step="0.01" required>
                        <div class="invalid-feedback" id="error-amount"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="save-ghost">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

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
        $('#ghostForm .form-control').removeClass('is-invalid');
        $('#ghostForm .invalid-feedback').text('');
    }

    function setInvalid(id, message) {
        $('#' + id).addClass('is-invalid');
        $('#error-' + id).text(message);
    }

    function resetForm() {
        $('#ghostForm')[0].reset();
        $('#ghost-id').val('');
        clearValidation();
    }

    $('#add-ghost').on('click', function () {
        resetForm();
        $('#ghostModalLabel').text('Add Ghost Plan');
        $('#save-ghost').text('Save');
        $('#ghostModal').modal('show');
    });

    $(document).on('click', '.edit-ghost', function () {
        resetForm();

        $('#ghostModalLabel').text('Edit Ghost Plan');
        $('#save-ghost').text('Update');

        $('#ghost-id').val($(this).data('id'));
        $('#tag_en').val($(this).data('tag-en'));
        $('#tag_ge').val($(this).data('tag-ge'));
        $('#title_en').val($(this).data('title-en'));
        $('#title_ge').val($(this).data('title-ge'));
        $('#duration_value').val($(this).data('duration-value'));
        $('#unit').val($(this).data('unit'));
        $('#duration_en').val($(this).data('duration-en'));
        $('#duration_ge').val($(this).data('duration-ge'));
        $('#amount').val($(this).data('amount'));

        $('#ghostModal').modal('show');
    });

    $('#ghostForm').on('submit', function (e) {
        e.preventDefault();
        clearValidation();

        var id = $('#ghost-id').val();
        var tagEn = $('#tag_en').val().trim();
        var tagGe = $('#tag_ge').val().trim();
        var titleEn = $('#title_en').val().trim();
        var titleGe = $('#title_ge').val().trim();
        var durationValue = $('#duration_value').val().trim();
        var unit = $('#unit').val();
        var durationEn = $('#duration_en').val().trim();
        var durationGe = $('#duration_ge').val().trim();
        var amount = $('#amount').val().trim();

        var hasError = false;

        if (!tagEn) { setInvalid('tag_en', 'English tag is required.'); hasError = true; }
        if (!tagGe) { setInvalid('tag_ge', 'German tag is required.'); hasError = true; }
        if (!titleEn) { setInvalid('title_en', 'English title is required.'); hasError = true; }
        if (!titleGe) { setInvalid('title_ge', 'German title is required.'); hasError = true; }
        if (!durationValue || isNaN(durationValue) || parseInt(durationValue) < 1 || parseInt(durationValue) > 365) {
            setInvalid('duration_value', 'Duration value must be between 1 and 365.');
            hasError = true;
        }
        if (!unit || (unit !== 'days' && unit !== 'hours')) {
            setInvalid('unit', 'Unit must be days or hours.');
            hasError = true;
        }
        if (!durationEn) { setInvalid('duration_en', 'English duration text is required.'); hasError = true; }
        if (!durationGe) { setInvalid('duration_ge', 'German duration text is required.'); hasError = true; }
        if (!amount || isNaN(amount) || parseFloat(amount) < 0 || parseFloat(amount) > 99999999.99) {
            setInvalid('amount', 'Amount must be between 0 and 99999999.99.');
            hasError = true;
        }

        if (hasError) return;

        var formData = new FormData(this);
        var url = id ? "{{ url('admin/ghosts') }}/" + id : "{{ route('admin.ghost.store') }}";

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
            beforeSend: function () {
                $('#save-ghost').prop('disabled', true).text('Saving...');
            },
            success: function (response) {
                if (response.status == 1) {
                    $('#ghostModal').modal('hide');
                    Swal.fire({
                        title: 'Success!',
                        text: response.message || 'Saved successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    }).then(function () {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Operation failed.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                }
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;

                    $.each(errors, function (key, value) {
                        var fieldMap = {
                            'tag_translation.en': 'tag_en',
                            'tag_translation.ge': 'tag_ge',
                            'title_translation.en': 'title_en',
                            'title_translation.ge': 'title_ge',
                            'duration_translation.en': 'duration_en',
                            'duration_translation.ge': 'duration_ge',
                            'duration_value': 'duration_value',
                            'unit': 'unit',
                            'amount': 'amount'
                        };

                        if (fieldMap[key]) {
                            setInvalid(fieldMap[key], value[0]);
                        }
                    });
                } else {
                    var msg = extractXhrMessage(xhr, 'Something went wrong. Please try again.');
                    Swal.fire({
                        title: 'Error!',
                        text: msg,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                }
            },
            complete: function () {
                $('#save-ghost').prop('disabled', false).text(id ? 'Update' : 'Save');
            }
        });
    });

    $(document).on('click', '.delete-ghost', function () {
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
                    url: "{{ url('admin/ghosts') }}/" + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status == 1) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message || 'Ghost Plan deleted.',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6'
                            }).then(function () {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Cannot delete',
                                text: response.message || 'Delete failed.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#d33'
                            });
                        }
                    },
                    error: function (xhr) {
                        var msg = extractXhrMessage(xhr, 'Something went wrong. Please try again.');
                        Swal.fire({
                            title: 'Error!',
                            text: msg,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            }
        });
    });

    $('#ghostModal').on('hidden.bs.modal', function () {
        resetForm();
        $('#ghostModalLabel').text('Add Ghost Plan');
        $('#save-ghost').text('Save');
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