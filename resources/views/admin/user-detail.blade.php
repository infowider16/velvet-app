@extends('layouts.admin')

@section('title', 'User Detail')

@section('styles')
<style>
.profile-image-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #007bff;
    margin-bottom: 20px;
}
.user-gallery-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.user-gallery-image:hover {
    transform: scale(1.02);
}
.user-media-upload {
    border: 1px solid #e9ecef;
    background: #f8fbff;
    border-radius: .85rem;
    box-shadow: 0 0.35rem 0.85rem rgba(106, 115, 125, 0.08);
}
.user-media-upload .upload-controls {
    width: 100%;
}
.user-media-upload label.btn {
    min-width: 140px;
}
.user-media-upload #selectedUserImageName {
    min-width: 180px;
}
.user-media-error {
    display: block;
    color: #dc3545;
    margin-top: 0.5rem;
}
.user-media-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: .85rem;
}
.user-media-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 1rem 1.5rem rgba(0, 0, 0, 0.12);
}
.user-media-delete-btn {
    top: 0.75rem;
    right: 0.75rem;
    z-index: 10;
    width: 2.2rem;
    height: 2.2rem;
    padding: 0;
    border-radius: 50%;
}
.user-media-empty {
    border: 2px dashed #dee2e6;
}
#user-map {
    width: 100%;
    height: 250px;
    border-radius: 8px;
    margin-top: 10px;
    border: 2px solid #eee;
}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
<!-- Bootstrap Tabs CSS (if not already included) -->
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">User Details</h4>
                        <div class="action-buttons-box" style="margin: 0;">
                            @if($user->is_delete != 1)
                                @php
                                    $isBlocked = $user->is_approve == 1;
                                    $toggleStatus = $isBlocked ? 0 : 1;
                                    $btnClass = $isBlocked ? 'btn-success' : 'btn-danger';
                                    $btnText = $isBlocked ? 'Unblock' : 'Block';
                                @endphp
                            
                                <button type="button"
                                    class="btn {{ $btnClass }} btn-sm text-nowrap"
                                    onclick="commonStatusChange({
                                        id: {{ $user->id }},
                                        status: {{ $toggleStatus }},
                                        url: '{{ route('admin.user.toggleStatus') }}',
                                        button: this
                                    })">
                                    <i class="fa fa-ban"></i> {{ $btnText }}
                                </button>
                            
                                <button type="button"
                                    class="btn btn-danger btn-sm text-nowrap"
                                    onclick="commonDelete({
                                        id: {{ $user->id }},
                                        url: '{{ route('admin.user.destroy', ':id') }}',
                                        button: this,
                                        message: 'Are you sure you want to delete this user?'
                                    })">
                                    Delete
                                </button>
                            @endif
                            <button type="button" 
                                class="btn btn-secondary btn-sm mr-3"
                                onclick="window.history.back()"
                                title="Go Back">
                                <i class="fa fa-arrow-left"></i> Back
                            </button>
                        </div>
                    </div>
                    <ul class="nav nav-tabs mb-3" id="userTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" role="tab" aria-controls="basic" aria-selected="true">
                                Basic Details
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="media-tab" data-toggle="tab" href="#media" role="tab" aria-controls="media" aria-selected="false">
                                Media
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content" id="userTabContent">
                        <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    @php
                                        $profileImage = $user->profile_image 
                                            ? asset('storage/' . $user->profile_image) 
                                            : asset('assets/images/user-default.jpg');
                                    @endphp
                                    <a href="{{ $profileImage }}" data-lightbox="profile-image" data-title="{{ $user->name }}">
                                        <img src="{{ $profileImage }}" alt="Profile Image" class="profile-image-preview">
                                    </a>
                                    <div class="mt-2">
                                        <span class="badge {{ $user->status_badge_class }}">
                                            {{ $user->status_label }}
                                        </span>
                                    </div>
                                    
                                    <!-- Deleted Status Badge -->
                                    @if($user->is_delete == 1)
                                        <div class="mt-2">
                                            <span class="badge badge-danger">
                                                User Deleted
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-8">
                                    <table class="table table-borderless table-responsive">
                                        <tr>
                                            <th>Name:</th>
                                            <td>{{ $user->name ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Google Id:</th>
                                            <td>{{ $user->google_id ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Gmail ID:</th>
                                            <td>{{ $user->gmail_id ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone:</th>
                                            <td>
                                                {{ $user->phone_code ? '+' . $user->phone_code . ' ' : '' }}
                                                {{ $user->phone_number ?: '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                <span class="badge {{ $user->status_badge_class }}">
                                                    {{ $user->status_label }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Created At:</th>
                                            <td>{{ $user->created_at ? date('Y-m-d H:i', strtotime($user->created_at)) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date of Birth:</th>
                                            <td>{{ $user->date_of_birth ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Gender:</th>
                                            <td>{{ $user->gender ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>About Me:</th>
                                            <td>{{ $user->about_me ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Location:</th>
                                            <td>{{ $user->location ?: '-' }}</td>
                                        </tr>
                                        
                                        <tr>
                                            <th>Location Consent:</th>
                                            <td>
                                                <span class="badge {{ $user->location_consent ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $user->location_consent_label ?? ($user->location_consent ? 'Yes' : 'No') }}
                                                </span>
                                            </td>
                                        </tr>
                                        @php
                                          
                                            
                                            $activeUntil = null;
                                            $isActive = false;
                                            
                                            if ($user && $user->gost_expire) {
                                                $expireTime = convertTimezone($user->gost_expire, null, null);
                                                $currentTime = convertTimezone(now(), null, null);
                                                
                                                // Check if ghost_expire is in the future
                                                if ($expireTime && $currentTime && $expireTime->greaterThan($currentTime)) {
                                                    $isActive = true;
                                                    $activeUntil = convertTimezone($user->gost_expire, null, 'Y-m-d H:i:s');
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <th>Ghost Modus :</th>
                                            <td>
                                                @if($isActive && $activeUntil)
                                                    <span class="badge bg-success">Active Until  : {{ $activeUntil }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Expired</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Latitude / Longitude:</th>
                                            <td>
                                                @if($user->lat && $user->lng)
                                                    {{ $user->lat }}, {{ $user->lng }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    @if($user->lat && $user->lng)
                                        <div id="user-map"></div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="media" role="tabpanel" aria-labelledby="media-tab">

                            <div class="card border-0 shadow-sm">

                                <div class="card-header bg-light d-flex justify-content-between align-items-center">

                                    <div>
                                        <h6 class="mb-0">
                                            <i class="fas fa-images text-primary mr-2"></i>
                                            User Media Gallery
                                        </h6>
                                        <small class="text-muted">Upload and manage the user&rsquo;s gallery images.</small>
                                    </div>

                                </div>

                                <div class="card-body">

                                    <div class="user-media-upload mb-4 p-3">
                                        <form id="addUserImageForm" enctype="multipart/form-data" class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 flex-wrap upload-controls">

                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">

                                            <div class="d-flex flex-column flex-sm-row align-items-center gap-2 flex-fill">
                                                <label class="btn btn-outline-primary btn-sm mb-0" for="userImageInput">
                                                    <i class="fas fa-upload mr-1"></i> Choose Image
                                                </label>

                                                <input type="file"
                                                    id="userImageInput"
                                                    name="image"
                                                    accept="image/*"
                                                    class="d-none">

                                                <div id="selectedUserImageName" class="text-truncate text-muted flex-fill">No file selected</div>
                                            </div>

                                            <div class="d-flex flex-column align-items-start align-items-md-end">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                                <small class="text-muted mt-2">Supported formats: JPG, PNG, GIF</small>
                                            </div>

                                            <div class="w-100 mt-2">
                                                <div id="selectedUserImageError" class="user-media-error" style="display:none;">
                                                    Please select an image first.
                                                </div>
                                            </div>

                                        </form>
                                    </div>

                                    @php
                                        $images = [];

                                        if (!empty($user->images)) {
                                            $images = is_array($user->images)
                                                ? $user->images
                                                : json_decode($user->images, true);
                                        }
                                    @endphp

                                    @if(!empty($images) && count($images))

                                        <div class="row gx-2 gy-3">

                                            @foreach($images as $img)

                                                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">

                                                    <div class="card user-media-card border-0 shadow-sm overflow-hidden position-relative">

                                                        <button type="button"
                                                                class="btn btn-danger btn-sm position-absolute user-media-delete-btn"
                                                                onclick='deleteUserImage({
                                                                    userId: {{ $user->id }},
                                                                    image: "{{ $img }}"
                                                                })'
                                                                aria-label="Delete image">
                                                            x
                                                        </button>

                                                        <a href="{{ asset('storage/' . $img) }}" data-lightbox="user-gallery" data-title="{{ $user->name }}">
                                                            <img src="{{ asset('storage/' . $img) }}"
                                                                class="user-gallery-image"
                                                                alt="User image">
                                                        </a>

                                                    </div>

                                                </div>

                                            @endforeach

                                        </div>

                                    @else

                                        <div class="text-center text-muted py-5 border rounded user-media-empty">

                                            <i class="fas fa-image fa-3x mb-3"></i>
                                            <p class="mb-1 font-weight-bold">No images uploaded yet</p>
                                            <p class="mb-0 small">Use the upload box above to add new media to this user.</p>

                                        </div>

                                    @endif

                                </div>

                            </div>

                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($user->lat && $user->lng)
<script>
function initMap() {
    var userLatLng = { lat: parseFloat("{{ $user->lat }}"), lng: parseFloat("{{ $user->lng }}") };
    var map = new google.maps.Map(document.getElementById('user-map'), {
        zoom: 14,
        center: userLatLng
    });
    new google.maps.Marker({
        position: userLatLng,
        map: map,
        title: "{{ $user->name }}"
    });
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer></script>
@endif
<script>
$(function () {
    // Bootstrap 4/5 tab activation
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // If you want to do something on tab change
    });
});

function deleteUserImage(data)
{
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this image?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({

                url: "{{ route('admin.user.delete-image') }}",

                type: "POST",

                data: {
                    _token: "{{ csrf_token() }}",
                    user_id: data.userId,
                    image: data.image
                },

                success: function(response) {

                    if (response.status) {

                        toastr.success(
                            response.message || 'Image deleted successfully'
                        );

                        setTimeout(() => {
                            location.reload();
                        }, 1000);

                    } else {

                        toastr.error(
                            response.message || 'Something went wrong'
                        );
                    }
                },

                error: function(xhr) {

                    toastr.error(
                        xhr.responseJSON?.message || 'Something went wrong'
                    );
                }
            });
        }
    });
}

$(function () {
    $('#userImageInput').on('change', function () {
        var fileName = this.files.length ? this.files[0].name : 'No file selected';
        $('#selectedUserImageName').text(fileName);
        $('#selectedUserImageError').hide();
    });

    $('#addUserImageForm').on('submit', function (e) {
        e.preventDefault();

        var fileInput = $('#userImageInput')[0];
        var formData = new FormData(this);
        var submitButton = $(this).find('button[type="submit"]');

        if (!fileInput.files || !fileInput.files.length) {
            $('#selectedUserImageError').text('Please select an image first.').show();
            return;
        }

        submitButton.prop('disabled', true).addClass('loading');

        $.ajax({
            url: '{{ route('admin.user.upload-image') }}',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.status) {
                    toastr.success(response.message || 'Image uploaded successfully');
                    setTimeout(function () {
                        location.reload();
                    }, 900);
                } else {
                    toastr.error(response.message || 'Unable to upload image');
                }
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Unable to upload image');
            },
            complete: function () {
                submitButton.prop('disabled', false).removeClass('loading');
            }
        });
    });
});
</script>
@include('admin.include.common-scripts')
@endsection






