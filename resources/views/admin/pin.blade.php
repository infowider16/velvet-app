@extends('layouts.admin')

@section('title', 'Pin Plan Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Pin Plan Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pin Plans</li>
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
                            <h3 class="card-title">Pin Plan List</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" id="add-pin">
                                    <i class="fas fa-plus"></i> Add Pin Plan
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="pin-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Sr No</th>
                                        <th>Tag</th>
                                        <th>Title</th>
                                        <th>Pin Count</th>
                                        <th>Discount (%)</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plans as $index => $plan)
                                        @php
                                            $tagTranslations = $plan->tag_translation ?? [];
                                            $titleTranslations = $plan->title_translation ?? [];
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $plan->tag }}</td>
                                            <td>{{ $plan->title }}</td>
                                            <td>{{ $plan->pin_count }}</td>
                                            <td>{{ $plan->discount }}</td>
                                            <td>{{ $plan->amount }}</td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info edit-pin"
                                                    data-id="{{ $plan->id }}"
                                                    data-tag-en="{{ $tagTranslations['en'] ?? $plan->tag }}"
                                                    data-tag-ge="{{ $tagTranslations['ge'] ?? '' }}"
                                                    data-title-en="{{ $titleTranslations['en'] ?? $plan->title }}"
                                                    data-title-ge="{{ $titleTranslations['ge'] ?? '' }}"
                                                    data-pin_count="{{ $plan->pin_count }}"
                                                    data-discount="{{ $plan->discount }}"
                                                    data-amount="{{ $plan->amount }}"
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger delete-pin"
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

<!-- Pin Modal -->
<div class="modal fade" id="pinModal" tabindex="-1" role="dialog" aria-labelledby="pinModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pinModalLabel">Add Pin Plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="pinForm">
                <div class="modal-body">
                    <input type="hidden" id="pin-id" name="id">

                    <div class="form-group">
                        <label for="tag_en">Tag (English)</label>
                        <input type="text" class="form-control" id="tag_en" name="tag_translation[en]" maxlength="255" placeholder="Best deal">
                        <div class="invalid-feedback" id="error-tag_en"></div>
                    </div>

                    <div class="form-group">
                        <label for="tag_ge">Tag (German)</label>
                        <input type="text" class="form-control" id="tag_ge" name="tag_translation[ge]" maxlength="255" placeholder="Hot Deal">
                        <div class="invalid-feedback" id="error-tag_ge"></div>
                    </div>

                    <div class="form-group">
                        <label for="title_en">Title (English)</label>
                        <input type="text" class="form-control" id="title_en" name="title_translation[en]" maxlength="255" placeholder="10 Pins">
                        <div class="invalid-feedback" id="error-title_en"></div>
                    </div>

                    <div class="form-group">
                        <label for="title_ge">Title (German)</label>
                        <input type="text" class="form-control" id="title_ge" name="title_translation[ge]" maxlength="255" placeholder="10 Pins">
                        <div class="invalid-feedback" id="error-title_ge"></div>
                    </div>

                    <div class="form-group">
                        <label for="pin_count">Pin Count <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="pin_count" name="pin_count" min="1" max="1000" required>
                        <div class="invalid-feedback" id="error-pin_count"></div>
                    </div>

                    <div class="form-group">
                        <label for="discount">Discount (%)</label>
                        <input type="number" class="form-control" id="discount" name="discount" min="0" max="100" step="0.01">
                        <div class="invalid-feedback" id="error-discount"></div>
                    </div>

                    <div class="form-group">
                        <label for="amount">Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount" name="amount" min="0" max="99999999.99" step="0.01" required>
                        <div class="invalid-feedback" id="error-amount"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="save-pin">Save</button>
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
        $('#pinForm .form-control').removeClass('is-invalid');
        $('#pinForm .invalid-feedback').text('');
    }

    function setInvalid(id, message) {
        $('#' + id).addClass('is-invalid');
        $('#error-' + id).text(message);
    }

    function resetForm() {
        $('#pinForm')[0].reset();
        $('#pin-id').val('');
        clearValidation();
    }

    $('#add-pin').on('click', function () {
        resetForm();
        $('#pinModalLabel').text('Add Pin Plan');
        $('#save-pin').text('Save');
        $('#pinModal').modal('show');
    });

    $(document).on('click', '.edit-pin', function () {
        resetForm();

        $('#pinModalLabel').text('Edit Pin Plan');
        $('#save-pin').text('Update');

        $('#pin-id').val($(this).data('id'));
        $('#tag_en').val($(this).data('tag-en'));
        $('#tag_ge').val($(this).data('tag-ge'));
        $('#title_en').val($(this).data('title-en'));
        $('#title_ge').val($(this).data('title-ge'));
        $('#pin_count').val($(this).data('pin_count'));
        $('#discount').val($(this).data('discount'));
        $('#amount').val($(this).data('amount'));

        $('#pinModal').modal('show');
    });

    $('#pinForm').on('submit', function (e) {
        e.preventDefault();
        clearValidation();

        var id = $('#pin-id').val();
        var tagEn = $('#tag_en').val().trim();
        var tagGe = $('#tag_ge').val().trim();
        var titleEn = $('#title_en').val().trim();
        var titleGe = $('#title_ge').val().trim();
        var pinCount = $('#pin_count').val().trim();
        var discount = $('#discount').val().trim();
        var amount = $('#amount').val().trim();

        var hasError = false;

        if (tagEn.length > 255) {
            setInvalid('tag_en', 'English tag must be at most 255 characters.');
            hasError = true;
        }

        if (tagGe.length > 255) {
            setInvalid('tag_ge', 'German tag must be at most 255 characters.');
            hasError = true;
        }

        if (titleEn.length > 255) {
            setInvalid('title_en', 'English title must be at most 255 characters.');
            hasError = true;
        }

        if (titleGe.length > 255) {
            setInvalid('title_ge', 'German title must be at most 255 characters.');
            hasError = true;
        }

        if (!pinCount || isNaN(pinCount) || parseInt(pinCount) < 1 || parseInt(pinCount) > 1000) {
            setInvalid('pin_count', 'Pin count must be between 1 and 1000.');
            hasError = true;
        }

        if (discount !== '') {
            var parsedDiscount = parseFloat(discount);
            if (isNaN(parsedDiscount) || parsedDiscount < 0 || parsedDiscount > 100) {
                setInvalid('discount', 'Discount must be between 0 and 100.');
                hasError = true;
            }
        }

        if (!amount || isNaN(amount) || parseFloat(amount) < 0 || parseFloat(amount) > 99999999.99) {
            setInvalid('amount', 'Amount must be between 0 and 99999999.99.');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        var formData = new FormData(this);
        var url = id ? "{{ url('admin/pins') }}/" + id : "{{ route('admin.pin.store') }}";

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
                $('#save-pin').prop('disabled', true).text('Saving...');
            },
            success: function (response) {
                if (response.status == 1) {
                    $('#pinModal').modal('hide');
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
                            'pin_count': 'pin_count',
                            'discount': 'discount',
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
                $('#save-pin').prop('disabled', false).text(id ? 'Update' : 'Save');
            }
        });
    });

    $(document).on('click', '.delete-pin', function () {
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
                    url: "{{ url('admin/pins') }}/" + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status == 1) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message || 'Pin Plan deleted.',
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

    $('#pinModal').on('hidden.bs.modal', function () {
        resetForm();
        $('#pinModalLabel').text('Add Pin Plan');
        $('#save-pin').text('Save');
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