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

#user-groups-table td .group-cell,
#user-groups-table td .creator-cell,
#user-group-members-table td .group-cell,
#user-group-reports-table td .group-cell {
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: normal;
    min-width: 0;
    max-width: 240px;
}

#user-groups-table td .group-cell img,
#user-groups-table td .creator-cell img,
#user-group-members-table td .group-cell img {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 8px;
}

#user-groups-table td .creator-cell img,
#user-group-members-table td .group-cell img {
    border-radius: 50%;
}

#user-groups-table td .group-meta,
#user-groups-table td .creator-meta,
#user-group-members-table td .group-meta {
    min-width: 0;
    max-width: 250px;
}

#user-groups-table td .group-meta .name,
#user-groups-table td .creator-meta .name,
#user-group-members-table td .group-meta .name {
    font-weight: 600;
    margin-bottom: 2px;
    display: block;
    white-space: normal;
    overflow: hidden;
    text-overflow: ellipsis;
}

#user-group-members-table td .group-meta .meta-label,
#user-groups-table td .group-meta .meta-label,
#user-groups-table td .creator-meta .meta-label {
    font-size: 12px;
    color: #6c757d;
}

#user-group-members-table td .group-meta .description {
    font-size: 12px;
    color: #6c757d;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 4px;
    max-height: 2.8rem;
}

#user-groups-table td .group-meta .name,
#user-groups-table td .creator-meta .name {
    font-weight: 600;
    margin-bottom: 2px;
}

#user-groups-table td .group-meta .meta-label,
#user-groups-table td .creator-meta .meta-label {
    font-size: 12px;
    color: #6c757d;
}

/* Enhanced Modal Styles */
.group-detail-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px 8px 0 0;
    border: none;
}

.group-detail-modal-header .modal-title {
    font-weight: 600;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.group-detail-modal-header .modal-title i {
    font-size: 1.5rem;
}

.group-detail-content {
    padding: 1.25rem 1.5rem 1.5rem;
}

.group-detail-modal-header {
    border-radius: 1rem 1rem 0 0;
}

#groupDetailModal .modal-content {
    border-radius: 1rem;
    overflow: hidden;
    max-width: 360px;
    width: 100%;
    margin: 0 auto;
    border: 2px solid #dc3545;
    box-shadow: 0 0 0 1px rgba(220, 53, 69, 0.15);
}

#groupDetailModal .modal-dialog {
    max-width: 360px;
    width: 100%;
    margin: 1.5rem auto;
}

#groupDetailModal .modal-dialog.modal-sm {
    max-width: 340px;
}

#groupDetailModal .modal-header .close {
    font-size: 1.5rem;
    opacity: 1;
    color: #ffffff;
}

.detail-item {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.detail-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.detail-label {
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-label i {
    color: #667eea;
    font-size: 1rem;
}

.detail-value {
    color: #212529;
    font-size: 1rem;
    font-weight: 500;
    word-break: break-word;
}

.detail-item.text-center .detail-value {
    font-size: 1.05rem;
    margin-bottom: 0.25rem;
}

.group-detail-image {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 16px;
    border: 2px solid rgba(255,255,255,0.35);
}

.detail-value.status-badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.detail-value.status-on {
    background-color: #d4edda;
    color: #155724;
}

.detail-value.status-off {
    background-color: #f8d7da;
    color: #721c24;
}

.detail-value.status-allowed {
    background-color: #d1ecf1;
    color: #0c5460;
}

.detail-value.status-not-allowed {
    background-color: #f8d7da;
    color: #721c24;
}

/* Group Members Styles */
.group-members-modal-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px 8px 0 0;
    border: none;
}

.group-members-modal-header .modal-title {
    font-weight: 600;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.group-members-modal-header .modal-title i {
    font-size: 1.5rem;
}

.members-content {
    padding: 1rem 1.25rem;
}

#groupMembersModal .modal-dialog {
    max-width: 420px;
    width: 100%;
}

#groupMembersModal .modal-content {
    border-radius: 1rem;
    overflow: hidden;
    max-width: 420px;
    margin: 0 auto;
}

#groupMembersModal .modal-header .close {
    font-size: 1.4rem;
    opacity: 1;
    color: #fff;
}

.member-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.member-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    background-color: #fff;
    border-color: #667eea;
}

.member-card .member-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 1rem;
    border: 2px solid #667eea;
}

.member-card .member-info {
    flex-grow: 1;
}

.member-card .member-name {
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
    font-size: 1rem;
}

.member-card .member-meta {
    font-size: 0.875rem;
    color: #6c757d;
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.member-card .member-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    background-color: #e7f3ff;
    color: #004085;
}

.member-card .member-badge.admin {
    background-color: #fff3cd;
    color: #856404;
}

.member-empty {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
    font-style: italic;
}

.modal-content {
    border-radius: 8px;
    border: none;
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}

.modal-header .btn-close {
    background-color: rgba(255, 255, 255, 0.8);
}

.modal-header .btn-close:hover {
    background-color: rgba(255, 255, 255, 1);
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

                        <li class="nav-item">
                            <a class="nav-link" id="members-tab" data-toggle="tab" href="#members">
                              Member Of Group 
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="pins-tab" data-toggle="tab" href="#pins">
                              Pins
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

                                <div class="card-header bg-light">
                                    <div>
                                        <h6 class="mb-0">
                                            <i class="fas fa-images text-primary mr-2"></i>
                                            Media & Documents
                                        </h6>
                                        <small class="text-muted">Gallery images and shared message attachments appear below.</small>
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
                                        $groupMedia = collect();
                                        $messageItems = collect();

                                        if (!empty($user->images)) {
                                            $images = is_array($user->images)
                                                ? $user->images
                                                : json_decode($user->images, true);
                                        }

                                        if (!empty($user->sentMessages)) {
                                            $messageItems = $messageItems->concat($user->sentMessages);
                                        }

                                        if (!empty($user->receivedMessages)) {
                                            $messageItems = $messageItems->concat($user->receivedMessages);
                                        }

                                        foreach ($messageItems as $message) {
                                            if (!empty($message->document_url)) {
                                                $groupMedia->push([
                                                    'type' => 'document',
                                                    'url' => asset('storage/' . ltrim($message->document_url, '/')),
                                                    'label' => basename($message->document_url),
                                                    'icon' => 'fas fa-file-alt',
                                                ]);
                                            } elseif (!empty($message->media_url) && $message->media_type === 'image') {
                                                $groupMedia->push([
                                                    'type' => 'image',
                                                    'url' => asset('storage/' . ltrim($message->media_url, '/')),
                                                    'label' => basename($message->media_url),
                                                    'icon' => 'fas fa-image',
                                                ]);
                                            } elseif (!empty($message->media_url)) {
                                                $groupMedia->push([
                                                    'type' => 'file',
                                                    'url' => asset('storage/' . ltrim($message->media_url, '/')),
                                                    'label' => basename($message->media_url),
                                                    'icon' => 'fas fa-file',
                                                ]);
                                            } elseif (!empty($message->link_url)) {
                                                $groupMedia->push([
                                                    'type' => 'link',
                                                    'url' => $message->link_url,
                                                    'label' => $message->link_url,
                                                    'icon' => 'fas fa-link',
                                                ]);
                                            }
                                        }

                                        $groupMedia = $groupMedia->unique('url');
                                    @endphp

                                    <div class="mt-4">
                                        <h6 class="mb-3">Uploaded Images</h6>

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

                                                            <a href="{{ asset('storage/' . $img) }}" data-lightbox="user-gallery" data-title="{{ basename($img) }}">
                                                                <img src="{{ asset('storage/' . $img) }}"
                                                                    class="user-gallery-image"
                                                                    alt="{{ basename($img) }}">
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-5 border rounded user-media-empty">
                                                <i class="fas fa-image fa-3x mb-3"></i>
                                                <p class="mb-1 font-weight-bold">No uploaded images yet</p>
                                                <p class="mb-0 small">Upload images from the form above to populate this section.</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-5">
                                        <h6 class="mb-3">Group Media</h6>

                                        @if($groupMedia->count())
                                            <div class="row gx-2 gy-3">
                                                @foreach($groupMedia as $item)
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="card border-0 shadow-sm p-3 h-100">
                                                            @if($item['type'] === 'image')
                                                                <a href="{{ $item['url'] }}" data-lightbox="group-media-gallery" data-title="{{ $item['label'] }}">
                                                                    <img src="{{ $item['url'] }}" class="user-gallery-image" alt="{{ $item['label'] }}">
                                                                </a>
                                                                <div class="mt-3 d-flex justify-content-between align-items-center">
                                                                    <div class="text-truncate" style="max-width: 180px;">{{ $item['label'] }}</div>
                                                                    <span class="small text-muted">Image</span>
                                                                </div>
                                                            @else
                                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                                    <div>
                                                                        <div class="font-weight-bold text-truncate" style="max-width: 180px;">{{ $item['label'] }}</div>
                                                                        <div class="small text-muted">{{ ucfirst($item['type']) }}</div>
                                                                    </div>
                                                                    <i class="{{ $item['icon'] }} fa-2x text-primary"></i>
                                                                </div>
                                                            @endif
                                                            <a href="{{ $item['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">Open</a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-5 border rounded user-media-empty">
                                                <i class="fas fa-folder-open fa-3x mb-3"></i>
                                                <p class="mb-1 font-weight-bold">No group media found</p>
                                                <p class="mb-0 small">Media shared in user messages will appear here.</p>
                                            </div>
                                        @endif
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="tab-pane fade" id="members">
                            <div class="">
                                <table id="user-group-members-table" class="table-responsive table table-bordered table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Group</th>
                                            <th>Member</th>
                                            <th>Role</th>
                                            <th>Permission</th>
                                            <th>Status</th>
                                            <th>Unread</th>
                                            <th>Accepted At</th>
                                            <th>Joined At</th>
                                            <th>Group Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pins">
                            <div class="">
                                <table id="user-pins-table" class="table-responsive table table-bordered table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Pin Message</th>
                                            <th>Country Code</th>
                                            <th>Total Likes</th>
                                            <th>Comments</th>
                                            <th>Status</th>
                                            <th>Commented On</th>
                                            <th>Created At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="groupDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header group-detail-modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users"></i>
                    Group Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body group-detail-content" id="groupDetailBody">

            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="groupMembersModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header group-members-modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-friends"></i>
                    Group Members
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body members-content" id="groupMembersBody">

            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="pinCommentsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document" style="max-width: 550px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 1.25rem 1.5rem;">
                <h5 class="modal-title">
                    <i class="fas fa-comments mr-2"></i>
                    Pin Comments
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="pinCommentsBody" style="max-height: 65vh; overflow-y: auto; padding: 1.5rem;">
                <!-- Comments will be loaded here -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pinLikesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document" style="max-width: 550px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); color: white; border: none; padding: 1.25rem 1.5rem;">
                <h5 class="modal-title">
                    <i class="fas fa-heart mr-2"></i>
                    Pin Likes
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="pinLikesBody" style="max-height: 65vh; overflow-y: auto; padding: 1.5rem;">
                <!-- Likes will be loaded here -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pinReportsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document" style="max-width: 550px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border: none; padding: 1.25rem 1.5rem;">
                <h5 class="modal-title">
                    <i class="fas fa-flag mr-2"></i>
                    Pin Reports
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="pinReportsBody" style="max-height: 65vh; overflow-y: auto; padding: 1.5rem;">
                <!-- Reports will be loaded here -->
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
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
    });

    initUserDetailTables();
});

function initUserDetailTables() {
   
    if (!$.fn.dataTable.isDataTable('#user-group-members-table')) {
        $('#user-group-members-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.user.group-members', $user->id) }}",
            },
            columns: [
                { data: 'group_info', name: 'group.name', orderable: false, searchable: false },
                { data: 'member_info', name: 'member.name', orderable: false, searchable: false },
                { data: 'role', name: 'role' },
                { data: 'is_member_permission', name: 'is_member_permission', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'unread_count', name: 'unread_count' },
                { data: 'accepted_at', name: 'accepted_at' },
                { data: 'created_at', name: 'created_at' },
                { data: 'group_status', name: 'group_status' },
                { data: 'action', orderable:false, searchable:false }
            ],
            order: [[8, 'desc']],
            responsive: true,
        });
    }

    if (!$.fn.dataTable.isDataTable('#user-pins-table')) {
        $('#user-pins-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.user.pins', $user->id) }}",
            },
            columns: [
                { data: 'pin_message', name: 'pin_message', orderable: false, searchable: false },
                { data: 'country_code', name: 'country_code' },
                { data: 'total_like', name: 'total_like' },
                { data: 'comment_count', name: 'comment_count' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'commented_on', name: 'commented_on' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', orderable: false, searchable: false }
            ],
            order: [[6, 'desc']],
            responsive: true,
        });
    }

}

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

$(document).on('click', '.group-detail-btn', function () {

    let id = $(this).data('id');

    let url = "{{ route('admin.user.group-detail', ':id') }}";
    url = url.replace(':id', id);

    $.get(url, function (res) {

        if (res.status) {

            let group = res.data;
            
            let notificationStatus = group.notification_status == 1 ? 'On' : 'Off';
            let notificationClass = group.notification_status == 1 ? 'status-on' : 'status-off';
            
            let permissionStatus = group.is_member_permission == 1 ? 'Allowed' : 'Not Allowed';
            let permissionClass = group.is_member_permission == 1 ? 'status-allowed' : 'status-not-allowed';

            let memberCount = group.members ? group.members.length : 0;
            let groupImage = group.image ? (group.image.startsWith('http') ? group.image : '{{ asset('storage') }}/' + group.image) : '{{ asset('assets/images/no-image.png') }}';

            let html = `
                <div class="detail-item text-center">
                    <img src="${groupImage}" alt="${group.name || 'Group'}" class="group-detail-image mb-3" />
                    <div class="detail-value" style="font-size: 1.1rem; font-weight: 700;">${group.name || '-'}</div>
                    <div class="meta-label">Members: ${memberCount}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-align-left"></i>
                        Description
                    </div>
                    <div class="detail-value">${group.description || 'No description provided'}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-cube"></i>
                        Group Type
                    </div>
                    <div class="detail-value">${group.group_type == 0 ? 'Public' : 'Private' }</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-bell"></i>
                        Notifications
                    </div>
                    <div class="detail-value status-badge ${notificationClass}">
                        ${notificationStatus}
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-user-check"></i>
                        Member Permissions
                    </div>
                    <div class="detail-value status-badge ${permissionClass}">
                        ${permissionStatus}
                    </div>
                </div>
            `;

            $("#groupDetailBody").html(html);
            $("#groupDetailModal").modal('show');
        }
    });

});


$(document).on('click', '.group-members-btn', function () {

    let id = $(this).data('id');

    let url = "{{ route('admin.user.members-detail', ':id') }}";
    url = url.replace(':id', id);

    $.get(url, function (res) {

        if (res.status) {

            let html = '';

            const defaultUserAvatar = '{{ asset('assets/images/user-default.jpg') }}';
            const storageBaseUrl = '{{ asset('storage') }}';

            function getMemberAvatarUrl(user) {
                if (!user) {
                    return defaultUserAvatar;
                }

                let imagePath = user.image || '';
                if (!imagePath && user.images) {
                    try {
                        let parsed = typeof user.images === 'string' ? JSON.parse(user.images) : user.images;
                        if (Array.isArray(parsed) && parsed.length > 0) {
                            imagePath = parsed[0];
                        }
                    } catch (e) {
                        imagePath = '';
                    }
                }

                if (!imagePath) {
                    return defaultUserAvatar;
                }

                imagePath = imagePath.replace(/\\/g, '/');

                if (/^(?:https?:\/\/|\/\/)/i.test(imagePath)) {
                    return imagePath;
                }

                return storageBaseUrl + '/' + imagePath.replace(/^\/+/, '');
            }

            if (res.data && res.data.length > 0) {
                $.each(res.data, function (i, row) {
                    let roleClass = row.role === 'admin' ? 'admin' : '';
                    let roleBadge = row.role ? row.role.charAt(0).toUpperCase() + row.role.slice(1) : 'Member';
                    let permissionLabel = row.is_member_permission == 1 ? 'Allowed' : 'Not Allowed';
                    let permissionClass = row.is_member_permission == 1 ? 'status-allowed' : 'status-not-allowed';
                    let statusLabel = row.status == 1 ? 'Block' : row.status == 2 ? 'Leave' : 'Unblock';
                    let statusClass = row.status == 1 ? 'status-not-allowed' : (row.status == 2 ? 'status-not-allowed' : 'status-allowed');
                    let unreadCount = row.unread_count || 0;
                    let memberName = row.user ? row.user.name : '-';
                    let memberAvatar = getMemberAvatarUrl(row.user);
                    let acceptedDate = row.accepted_at ? new Date(row.accepted_at).toLocaleDateString() : 'Pending';
                    let groupStatus = row.group_status || '-';

                    html += `
                        <div class="member-card">
                            <img src="${memberAvatar}" alt="${memberName}" class="member-avatar" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(memberName)}&color=667eea&background=f0f0f0'">
                            <div class="member-info">
                                <div class="member-name">${memberName}</div>
                                <div class="member-meta">
                                    <span class="member-badge ${roleClass}">${roleBadge}</span>
                                    <span class="member-badge ${permissionClass}">${permissionLabel}</span>
                                    <span class="member-badge ${statusClass}">${statusLabel}</span>
                                </div>
                                <div class="member-meta" style="margin-top: 0.5rem; gap: 0.75rem;">
                                    <span>Unread: ${unreadCount}</span>
                                    <span>Group: ${groupStatus}</span>
                                    <span>Joined: ${acceptedDate}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="member-empty"><i class="fas fa-users" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i><p>No members found</p></div>';
            }

            $("#groupMembersBody").html(html);
            $("#groupMembersModal").modal('show');
        }
    });

});

$(document).on('click', '.pin-comments-btn', function () {
    let pinId = $(this).data('id');
    let url = "{{ route('admin.pin.comments', ':id') }}";
    url = url.replace(':id', pinId);

    $.get(url, function (res) {
        if (res.status) {
            let data = res.data;
            
            // Format pin message - limit to 100 chars
            let pinMessage = (data.pin_message || '-').substring(0, 100);
            if (data.pin_message && data.pin_message.length > 100) {
                pinMessage += '...';
            }
            
            let html = `
                <div class="pin-details-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.25rem; border-radius: 4px; margin-bottom: 1rem;">
                    <div class="mb-3">
                        <small style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;"><i class="fas fa-comment-dots mr-1"></i>Pin Message</small>
                        <p class="mb-0 text-break" style="font-size: 0.95rem; margin-top: 0.5rem; line-height: 1.5;">${pinMessage}</p>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; display: block; margin-bottom: 0.5rem;"><i class="fas fa-globe mr-1"></i>Country</small>
                            <span class="badge" style="background: rgba(255,255,255,0.25); padding: 0.5rem 0.75rem; font-size: 0.9rem;">${data.country_code}</span>
                        </div>
                        <div class="col-6 text-right">
                            <small style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; display: block; margin-bottom: 0.5rem;"><i class="fas fa-heart mr-1"></i>Total Likes</small>
                            <span class="badge" style="background: rgba(255,255,255,0.25); padding: 0.5rem 0.75rem; font-size: 0.9rem;">${data.total_likes}</span>
                        </div>
                    </div>
                </div>
                <div class="pin-comments-section">
                    <h6 class="mb-3 font-weight-bold" style="color: #2d3748; border-bottom: 2px solid #667eea; padding-bottom: 0.75rem;">
                        <i class="fas fa-comments" style="color: #667eea; margin-right: 0.75rem;"></i>
                        Comments <span style="background: #667eea; color: white; padding: 0.25rem 0.65rem; border-radius: 20px; font-size: 0.8rem; margin-left: 0.5rem;">${data.comment_count}</span>
                    </h6>
                    <div style="margin-top: 1rem;">
                        ${data.comments_html}
                    </div>
                </div>
            `;
            
            $("#pinCommentsBody").html(html);
            $("#pinCommentsModal").modal('show');
        } else {
            toastr.error(res.message || 'Error loading comments');
        }
    }).fail(function() {
        toastr.error('Error loading comments');
    });
});

$(document).on('click', '.pin-likes-btn', function () {
    let pinId = $(this).data('id');
    let url = "{{ route('admin.pin.likes', ':id') }}";
    url = url.replace(':id', pinId);

    $.get(url, function (res) {
        if (res.status) {
            let data = res.data;
            
            let html = `
                <div class="pin-details-section" style="background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); color: white; padding: 1.5rem; border-radius: 4px; margin-bottom: 1rem;">
                    <div class="mb-3">
                        <small style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;"><i class="fas fa-thumbtack mr-1"></i>Pin Message</small>
                        <p class="mb-0 text-break" style="font-size: 0.95rem; margin-top: 0.5rem; line-height: 1.5;">${data.pin_message}</p>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; display: block; margin-bottom: 0.5rem;"><i class="fas fa-globe mr-1"></i>Country</small>
                            <span class="badge" style="background: rgba(255,255,255,0.25); padding: 0.5rem 0.75rem; font-size: 0.9rem;">${data.pin_country_code}</span>
                        </div>
                        <div class="col-6 text-right">
                            <small style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; display: block; margin-bottom: 0.5rem;"><i class="fas fa-heart mr-1"></i>Total Likes</small>
                            <span class="badge" style="background: rgba(255,255,255,0.25); padding: 0.5rem 0.75rem; font-size: 0.9rem;">${data.pin_total_likes}</span>
                        </div>
                    </div>
                </div>
                <div class="pin-likes-section">
                    <h6 class="mb-3 font-weight-bold" style="color: #2d3748; border-bottom: 2px solid #f5576c; padding-bottom: 0.75rem;">
                        <i class="fas fa-heart" style="color: #f5576c; margin-right: 0.75rem;"></i>
                        People Who Liked <span style="background: #f5576c; color: white; padding: 0.25rem 0.65rem; border-radius: 20px; font-size: 0.8rem; margin-left: 0.5rem;">${data.likes_count}</span>
                    </h6>
                    <div style="margin-top: 1rem;">
                        ${data.likes_html}
                    </div>
                </div>
            `;
            
            $("#pinLikesBody").html(html);
            $("#pinLikesModal").modal('show');
        } else {
            toastr.error(res.message || 'Error loading likes');
        }
    }).fail(function() {
        toastr.error('Error loading likes');
    });
});

$(document).on('click', '.pin-reports-btn', function () {
    let pinId = $(this).data('id');
    let url = "{{ route('admin.pin.reports', ':id') }}";
    url = url.replace(':id', pinId);

    $.get(url, function (res) {
        if (res.status) {
            let data = res.data;
            
            let html = `
                <div class="pin-details-section" style="background: linear-gradient(135deg, #ffa500 0%, #ffb703 100%); color: white; padding: 1.5rem; border-radius: 4px; margin-bottom: 1rem;">
                    <div class="mb-3">
                        <small style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;"><i class="fas fa-map-pin mr-1"></i>Pin Message</small>
                        <p class="mb-0 text-break" style="font-size: 0.95rem; margin-top: 0.5rem; line-height: 1.5;">${data.pin_message}</p>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; display: block; margin-bottom: 0.5rem;"><i class="fas fa-globe mr-1"></i>Country</small>
                            <span class="badge" style="background: rgba(255,255,255,0.25); padding: 0.5rem 0.75rem; font-size: 0.9rem;">${data.pin_country_code}</span>
                        </div>
                        <div class="col-6 text-right">
                            <small style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; display: block; margin-bottom: 0.5rem;"><i class="fas fa-heart mr-1"></i>Total Likes</small>
                            <span class="badge" style="background: rgba(255,255,255,0.25); padding: 0.5rem 0.75rem; font-size: 0.9rem;">${data.pin_total_likes}</span>
                        </div>
                    </div>
                </div>
                <div class="pin-reports-section">
                    <h6 class="mb-3 font-weight-bold" style="color: #2d3748; border-bottom: 2px solid #ffa500; padding-bottom: 0.75rem;">
                        <i class="fas fa-flag" style="color: #ffa500; margin-right: 0.75rem;"></i>
                        Reports <span style="background: #ffa500; color: white; padding: 0.25rem 0.65rem; border-radius: 20px; font-size: 0.8rem; margin-left: 0.5rem;">${data.reports_count}</span>
                    </h6>
                    <div style="margin-top: 1rem;">
                        ${data.reports_html}
                    </div>
                </div>
            `;
            
            $("#pinReportsBody").html(html);
            $("#pinReportsModal").modal('show');
        } else {
            toastr.error(res.message || 'Error loading reports');
        }
    }).fail(function() {
        toastr.error('Error loading reports');
    });
});

</script>
@include('admin.include.common-scripts')
@endsection






