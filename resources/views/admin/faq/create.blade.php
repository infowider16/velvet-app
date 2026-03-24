@extends('layouts.admin')



@section('title', 'Create FAQ')



@section('content')

<div class="content-wrapper">

    <div class="row">

        <div class="col-md-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <h4 class="card-title">Create New FAQ</h4>

                        <a href="{{ route('admin.faq.index') }}" class="btn btn-secondary">Back to List</a>

                    </div>



                    <form id="faq-form" method="POST" action="{{ route('admin.faq.store') }}">

                        @csrf

                        <div class="form-group">

                            <label for="question">Question <span class="text-danger">*</span></label>

                            <input type="text" class="form-control" id="question" name="question" 

                                value="{{ old('question') }}" required>

                            <span class="text-danger" id="question-error"></span>

                        </div>



                        <div class="form-group">

                            <label for="answer">Answer <span class="text-danger">*</span></label>

                            <textarea class="form-control" id="answer" name="answer" rows="5" required>{{ old('answer') }}</textarea>

                            <span class="text-danger" id="answer-error"></span>

                        </div>



                        <button type="submit" class="btn btn-primary" id="submit-btn">

                            <span id="btn-text">Create FAQ</span>

                            <span id="btn-spinner" class="spinner-border spinner-border-sm ml-2" style="display: none;"></span>

                        </button>

                        <a href="{{ route('admin.faq.index') }}" class="btn btn-secondary">Cancel</a>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection



@section('scripts')

<script>

$(document).ready(function() {

    $('#faq-form').on('submit', function(e) {

        e.preventDefault();

        

        // Clear previous errors

        $('.text-danger').text('');

        $('.form-control').removeClass('is-invalid');

        

        // Disable submit button and show spinner

        const submitBtn = $('#submit-btn');

        const btnText = $('#btn-text');

        const btnSpinner = $('#btn-spinner');

        

        submitBtn.prop('disabled', true);

        btnText.text('Processing...');

        btnSpinner.show();

        

        const formData = new FormData(this);

        

        $.get("{{ route('refresh.csrf') }}", function(data) {

            formData.set('_token', data.token);

            

            $.ajax({

                url: "{{ route('admin.faq.store') }}",

                type: "POST",

                data: formData,

                processData: false,

                contentType: false,

                headers: {

                    'X-Requested-With': 'XMLHttpRequest'

                },

                success: function(response) {

                    if (response.success || (response.status === 1)) {

                        Swal.fire({

                            icon: 'success',

                            title: 'Success',

                            text: response.message,

                            timer: 2000,

                            showConfirmButton: false

                        }).then(() => {

                            window.location.href = "{{ route('admin.faq.index') }}";

                        });

                    } else {

                        Swal.fire({

                            icon: 'error',

                            title: 'Error',

                            text: response.message

                        });

                    }

                },

                error: function(xhr) {

                    if (xhr.status === 422) {

                        // Validation errors

                        const errors = xhr.responseJSON.errors;

                        $.each(errors, function(field, messages) {

                            $(`#${field}-error`).text(messages[0]);

                            $(`#${field}`).addClass('is-invalid');

                        });

                    } else if (xhr.status === 419) {

                        Swal.fire({

                            icon: 'error',

                            title: 'Session Expired',

                            text: 'Your session has expired. Please refresh the page.'

                        });

                    } else {

                        const errorMessage = xhr.responseJSON?.message || 'Something went wrong. Please try again.';

                        Swal.fire({

                            icon: 'error',

                            title: 'Error',

                            text: errorMessage

                        });

                    }

                },

                complete: function() {

                    // Re-enable submit button and hide spinner

                    submitBtn.prop('disabled', false);

                    btnText.text('Create FAQ');

                    btnSpinner.hide();

                }

            });

        });

    });

});

</script>

@endsection

