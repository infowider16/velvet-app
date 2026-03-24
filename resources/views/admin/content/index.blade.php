@extends('layouts.admin')

@section('title', 'Content Management')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Content Management</h4>
                    <p class="card-description">Manage your website content</p>

                    @foreach($contents as $content)
                        @php
                            $titleTranslation = is_array($content->title_translation)
                                ? $content->title_translation
                                : json_decode($content->title_translation, true);

                            $descriptionTranslation = is_array($content->description_translation)
                                ? $content->description_translation
                                : json_decode($content->description_translation, true);
                        @endphp
                   
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <button class="btn btn-primary btn-sm edit-content" 
                                data-id="{{ $content->id }}" 
                                data-title="{{ $content->title }}" 
                                data-title_translation="{{ $titleTranslation['ge'] ?? '' }}"
                                data-description="{{ json_encode($content->description) }}" 
                                data-description_translation="{{ $descriptionTranslation['ge'] ?? '' }}"
                                data-slug="{{ $content->slug ?? '' }}">
                                Edit
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><strong>Title:</strong></label>
                                        <p class="content-title">{{ $content->title }}</p>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><strong>German Title:</strong></label>
                                        <p class="content-title-translation">{{ $titleTranslation['ge'] ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><strong>Description:</strong></label>
                                        <div class="content-description">
                                            {!! $content->description !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><strong>German Description:</strong></label>
                                        <div class="content-description-translation">
                                            {!! $descriptionTranslation['ge'] ?? '-' !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Last Updated:</strong></label>
                                        <p>{{ $content->updated_at ? $content->updated_at->format('Y-m-d H:i:s') : '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    @if($contents->isEmpty())
                    <div class="text-center">
                        <p>No content found.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Content Modal -->
<div class="modal fade" id="editContentModal" tabindex="-1" role="dialog" aria-labelledby="editContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContentModalLabel">Edit Content</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="content-form">
                <div class="modal-body">
                    <input type="hidden" id="content-id" name="content_id">

                    <div class="form-group">
                        <label for="content-title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="content-title" name="title" required>
                        <span class="text-danger" id="title-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="content-title-translation">German Title</label>
                        <input type="text" class="form-control" id="content-title-translation" name="title_translation">
                        <span class="text-danger" id="title_translation-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="content-description">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content-description" name="description" rows="10" required></textarea>
                        <span class="text-danger" id="description-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="content-description-translation">German Description</label>
                        <textarea class="form-control" id="content-description-translation" name="description_translation" rows="10"></textarea>
                        <span class="text-danger" id="description_translation-error"></span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="save-content-btn">
                        <span id="save-btn-text">Update Content</span>
                        <span id="save-btn-spinner" class="spinner-border spinner-border-sm ml-2" style="display: none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $(document).on('click', '.edit-content', function(e) {
        e.preventDefault();

        let contentId = $(this).data('id');
        let title = $(this).data('title');
        let titleTranslation = $(this).data('title_translation');
        let description = $(this).data('description');
        let descriptionTranslation = $(this).data('description_translation');
        let slug = $(this).data('slug');

        try {
            if (typeof description === 'string' && description.startsWith('"')) {
                description = JSON.parse(description);
            }
        } catch (e) {
            console.log('Description parsing error:', e);
        }

        try {
            if (typeof descriptionTranslation === 'string' && descriptionTranslation.startsWith('"')) {
                descriptionTranslation = JSON.parse(descriptionTranslation);
            }
        } catch (e) {
            console.log('German description parsing error:', e);
        }

        $('.text-danger').text('');
        $('.form-control').removeClass('is-invalid');

        $('#content-id').val(contentId);
        $('#content-title').val(title);
        $('#content-title-translation').val(titleTranslation);

        $('#content-description').attr('data-content', description);
        $('#content-description-translation').attr('data-content', descriptionTranslation);

        $('#editContentModal').modal('show');
    });

    $('#editContentModal').on('shown.bs.modal', function () {
        if (!$('#content-description').next('.note-editor').length) {
            $('#content-description').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        }

        if (!$('#content-description-translation').next('.note-editor').length) {
            $('#content-description-translation').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        }

        setTimeout(function() {
            let content = $('#content-description').attr('data-content') || '';
            let translatedContent = $('#content-description-translation').attr('data-content') || '';

            $('#content-description').summernote('code', content);
            $('#content-description-translation').summernote('code', translatedContent);
        }, 200);
    });

    $('#content-form').on('submit', function(e) {
        e.preventDefault();

        $('.text-danger').text('');
        $('.form-control').removeClass('is-invalid');

        const submitBtn = $('#save-content-btn');
        const btnText = $('#save-btn-text');
        const btnSpinner = $('#save-btn-spinner');

        submitBtn.prop('disabled', true);
        btnText.text('Processing...');
        btnSpinner.show();

        const contentId = $('#content-id').val();
        const formData = new FormData();

        formData.append('title', $('#content-title').val());
        formData.append('title_translation', $('#content-title-translation').val());
        formData.append('description', $('#content-description').summernote('code'));
        formData.append('description_translation', $('#content-description-translation').summernote('code'));

        $.get("{{ route('refresh.csrf') }}", function(data) {
            formData.append('_token', data.token);
            formData.append('_method', 'PUT');

            let updateUrl = "{{ route('admin.content.update', ':id') }}".replace(':id', contentId);

            $.ajax({
                url: updateUrl,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success || response.status === 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
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
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            $(`#${field}-error`).text(messages[0]);
                            $(`[name="${field}"]`).addClass('is-invalid');
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
                    submitBtn.prop('disabled', false);
                    btnText.text('Update Content');
                    btnSpinner.hide();
                }
            });
        });
    });

    $('#editContentModal').on('hidden.bs.modal', function () {
        $('#content-form')[0].reset();

        if ($('#content-description').next('.note-editor').length) {
            $('#content-description').summernote('destroy');
        }

        if ($('#content-description-translation').next('.note-editor').length) {
            $('#content-description-translation').summernote('destroy');
        }

        $('#content-description').removeAttr('data-content');
        $('#content-description-translation').removeAttr('data-content');
        $('.text-danger').text('');
        $('.form-control').removeClass('is-invalid');
    });
});
</script>
@endsection