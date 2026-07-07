@extends('layouts.admin')

@section('title', 'Pin Detail')

@section('styles')
<style>
.profile-image-preview {
    width: 170px;
    height: 170px;
    object-fit: cover;
    border-radius: 15px;
    border: 4px solid #007bff;
}

.table th {
    width: 220px;
    font-weight: 600;
    background: #f8f9fa;
}

.badge-pin-active {
    background: #28a745;
    color: #fff;
    padding: 6px 12px;
    font-size: 0.85rem;
}

.badge-pin-inactive {
    background: #dc3545;
    color: #fff;
    padding: 6px 12px;
    font-size: 0.85rem;
}

/* Comment Styles */
.comment-card {
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.comment-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    background-color: #fff;
    border-color: #667eea;
}

.comment-card .comment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.comment-card .comment-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.comment-card .comment-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.comment-card .comment-user-details {
    display: flex;
    flex-direction: column;
}

.comment-card .comment-user-name {
    font-weight: 600;
    font-size: 0.95rem;
    color: #2d3748;
}

.comment-card .comment-user-email {
    font-size: 0.8rem;
    color: #718096;
}

.comment-card .comment-text {
    margin-top: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #fff;
    border-radius: 6px;
    border-left: 4px solid #667eea;
    color: #2d3748;
}

.comment-card .comment-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: #718096;
}

/* Like Styles */
.like-card {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.like-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    background-color: #fff;
    border-color: #f5576c;
}

.like-card .like-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
}

.like-card .like-user-name {
    font-weight: 600;
    color: #2d3748;
}

.like-card .like-user-email {
    font-size: 0.8rem;
    color: #718096;
    margin-left: 8px;
}

.like-card .like-date {
    margin-left: auto;
    font-size: 0.8rem;
    color: #718096;
}

/* Report Styles */
.report-card-pin {
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.report-card-pin:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    background-color: #fff;
    border-color: #dc3545;
}

.report-card-pin .report-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.report-card-pin .reporter-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.report-card-pin .reporter-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.report-card-pin .reporter-details {
    display: flex;
    flex-direction: column;
}

.report-card-pin .reporter-name {
    font-weight: 600;
    font-size: 0.95rem;
    color: #2d3748;
}

.report-card-pin .reporter-email {
    font-size: 0.8rem;
    color: #718096;
}

.report-card-pin .report-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.report-card-pin .report-reason {
    margin-top: 0.75rem;
    padding: 0.75rem 1rem;
    background: #fff;
    border-radius: 6px;
    border-left: 4px solid #dc3545;
}

.report-card-pin .report-reason-label {
    font-weight: 600;
    font-size: 0.85rem;
    color: #4a5568;
}

.report-card-pin .report-reason-text {
    margin-top: 4px;
    color: #2d3748;
    font-size: 0.95rem;
}

.report-card-pin .report-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

.report-card-pin .report-date {
    font-size: 0.85rem;
    color: #718096;
}

/* Modal Styles */
.pin-detail-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px 8px 0 0;
    border: none;
}

.comment-detail-modal-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px 8px 0 0;
    border: none;
}

.like-detail-modal-header {
    background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px 8px 0 0;
    border: none;
}

.report-detail-modal-header-pin {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px 8px 0 0;
    border: none;
}

#commentDetailModal .modal-content,
#likeDetailModal .modal-content,
#reportDetailModalPin .modal-content {
    border-radius: 1rem;
    overflow: hidden;
    max-width: 450px;
    width: 100%;
    margin: 0 auto;
}

#commentDetailModal .modal-dialog,
#likeDetailModal .modal-dialog,
#reportDetailModalPin .modal-dialog {
    max-width: 450px;
    width: 100%;
    margin: 1.5rem auto;
}

.detail-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #667eea;
}

.detail-item {
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
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
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.detail-value {
    color: #212529;
    font-size: 0.95rem;
    font-weight: 500;
    word-break: break-word;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state .font-weight-bold {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.empty-state .small {
    font-size: 0.9rem;
}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Pin Details</h4>
                        <div>
                            <button type="button" 
                                class="btn btn-secondary btn-sm"
                                onclick="window.history.back()"
                                title="Go Back">
                                <i class="fa fa-arrow-left"></i> Back
                            </button>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-4" id="pinTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" role="tab" aria-controls="basic" aria-selected="true">
                                Basic Details
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="comments-tab" data-toggle="tab" href="#comments" role="tab" aria-controls="comments" aria-selected="false">
                                Comments ({{ $pin->comments->count() }})
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="likes-tab" data-toggle="tab" href="#likes" role="tab" aria-controls="likes" aria-selected="false">
                                Likes ({{ $pin->likes->count() }})
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="reports-tab" data-toggle="tab" href="#reports" role="tab" aria-controls="reports" aria-selected="false">
                                Reports ({{ $pin->pinReports->count() }})
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="pinTabContent">
                        <!-- ==================== BASIC DETAILS TAB ==================== -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                            <div class="row">
                                <!-- Left Column - User Info -->
                                <div class="col-md-4 text-center">
                                    @php
                                        $userAvatar = asset('assets/images/user-default.jpg');
                                        if($pin->user) {
                                            if($pin->user->profile_image) {
                                                $userAvatar = asset('storage/' . $pin->user->profile_image);
                                            } elseif($pin->user->images) {
                                                $images = json_decode($pin->user->images, true);
                                                if(is_array($images) && count($images) > 0) {
                                                    $userAvatar = asset('storage/' . $images[0]);
                                                }
                                            }
                                        }
                                    @endphp
                                    <img src="{{ $userAvatar }}" class="profile-image-preview" alt="{{ $pin->user->name ?? 'Unknown' }}">
                                    <h4 class="mt-3 font-weight-bold m-2">{{ $pin->user->name ?? 'Unknown User' }}</h4>
                                    <p class="text-muted">{{ $pin->user?->gmail_id ?? $pin->user?->phone_code .''.  $pin->user?->phone_number;}}</p>
                                    <div class="mt-2">
                                        <span class="badge {{ $pin->status == 1 ? 'badge-pin-active' : 'badge-pin-inactive' }}">
                                            {{ $pin->status == 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt"></i> Created: {{ $pin->created_at ? date('d M Y', strtotime($pin->created_at)) : '-' }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Right Column - Pin Details Table -->
                                <div class="col-md-8">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 200px;">Pin ID</th>
                                            <td>#{{ $pin->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Pin Message</th>
                                            <td>
                                                <div class="p-2" style="background: #f8f9fa; border-radius: 6px; border-left: 4px solid #667eea;">
                                                    {{ $pin->pin_message ?? '-' }}
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>User</th>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $userAvatar }}" 
                                                         style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;" 
                                                         alt="{{ $pin->user->name ?? 'Unknown' }}">
                                                    <span class="m-2">{{ $pin->user->name ?? 'Unknown User' }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Country Code</th>
                                            <td>
                                                <span class="badge bg-info">{{ $pin->country_code ?? '-' }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <span class="badge {{ $pin->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $pin->status == 1 ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Total Likes</th>
                                            <td>
                                                <span class="badge bg-danger" style="font-size: 0.9rem;">
                                                    <i class="fas fa-heart"></i> {{ $pin->total_like ?? 0 }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Total Comments</th>
                                            <td>
                                                <span class="badge bg-primary" style="font-size: 0.9rem;">
                                                    <i class="fas fa-comment"></i> {{ $pin->comments->count() }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Total Reports</th>
                                            <td>
                                                <span class="badge bg-danger" style="font-size: 0.9rem;">
                                                    <i class="fas fa-flag"></i> {{ $pin->pinReports->count() }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Commented On</th>
                                            <td>{{ $pin->commented_on ? date('d M Y h:i A', strtotime($pin->commented_on)) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ $pin->created_at ? date('d M Y h:i A', strtotime($pin->created_at)) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Updated At</th>
                                            <td>{{ $pin->updated_at ? date('d M Y h:i A', strtotime($pin->updated_at)) : '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== COMMENTS TAB ==================== -->
                        <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                            <div class="row">
                                <div class="col-12">
                                    @if($pin->comments->count())
                                        <div class="mb-3">
                                            <span class="badge bg-primary" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                <i class="fas fa-comment"></i> Total Comments: {{ $pin->comments->count() }}
                                            </span>
                                        </div>

                                        @foreach($pin->comments as $comment)
                                            @php
                                                $commenterAvatar = asset('assets/images/user-default.jpg');
                                                $commenterName = 'Unknown User';
                                                $commenterEmail = 'No email';
                                                
                                                if($comment->user) {
                                                    $commenterName = $comment->user->name ?? 'Unknown User';
                                                    $commenterEmail = $comment->user?->gmail_id  ?? $comment->user?->phone_code .''.  $comment->user?->phone_number;
                                                    
                                                    if($comment->user->profile_image) {
                                                        $commenterAvatar = asset('storage/' . $comment->user->profile_image);
                                                    } elseif($comment->user->images) {
                                                        $images = json_decode($comment->user->images, true);
                                                        if(is_array($images) && count($images) > 0) {
                                                            $commenterAvatar = asset('storage/' . $images[0]);
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <div class="comment-card">
                                                <div class="comment-header">
                                                    <div class="comment-user-info">
                                                        <img src="{{ $commenterAvatar }}" class="comment-avatar" 
                                                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($commenterName) }}&color=667eea&background=f0f0f0&size=40'"
                                                             alt="{{ $commenterName }}">
                                                        <div class="comment-user-details">
                                                            <span class="comment-user-name">{{ $commenterName }}</span>
                                                            <span class="comment-user-email">{{ $commenterEmail }}</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-heart"></i> {{ $comment->total_like ?? 0 }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="comment-text">
                                                    {{ $comment->comment ?? 'No comment' }}
                                                </div>
                                                <div class="comment-meta">
                                                    <span>
                                                        <i class="far fa-calendar-alt"></i>
                                                        {{ $comment->commented_on ? date('d M Y h:i A', strtotime($comment->commented_on)) : ($comment->created_at ? date('d M Y h:i A', strtotime($comment->created_at)) : '-') }}
                                                    </span>
                                                    <button type="button" class="btn btn-sm btn-outline-primary comment-detail-btn" data-id="{{ $comment->id }}">
                                                        <i class="fas fa-info-circle"></i> Details
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="empty-state">
                                            <i class="fas fa-comment"></i>
                                            <div class="font-weight-bold">No comments found</div>
                                            <div class="small">No one has commented on this pin yet.</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- ==================== LIKES TAB ==================== -->
                        <div class="tab-pane fade" id="likes" role="tabpanel" aria-labelledby="likes-tab">
                            <div class="row">
                                <div class="col-12">
                                    @if($pin->likes->count())
                                        <div class="mb-3">
                                            <span class="badge bg-danger" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                <i class="fas fa-heart"></i> Total Likes: {{ $pin->likes->count() }}
                                            </span>
                                        </div>

                                        @foreach($pin->likes as $like)
                                            @php
                                                $likerAvatar = asset('assets/images/user-default.jpg');
                                                $likerName = 'Unknown User';
                                                $likerEmail = 'No email';
                                                
                                                if($like->user) {
                                                    $likerName = $like->user->name ?? 'Unknown User';
                                                    $likerEmail = $like->user?->gmail_id ?? $like->user?->phone_code .''.  $like->user?->phone_number;
                                                    
                                                    if($like->user->profile_image) {
                                                        $likerAvatar = asset('storage/' . $like->user->profile_image);
                                                    } elseif($like->user->images) {
                                                        $images = json_decode($like->user->images, true);
                                                        if(is_array($images) && count($images) > 0) {
                                                            $likerAvatar = asset('storage/' . $images[0]);
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <div class="like-card">
                                                <img src="{{ $likerAvatar }}" class="like-avatar" 
                                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($likerName) }}&color=f5576c&background=f0f0f0&size=35'"
                                                     alt="{{ $likerName }}">
                                                <div>
                                                    <span class="like-user-name">{{ $likerName }}</span>
                                                    <span class="like-user-email">{{ $likerEmail }}</span>
                                                </div>
                                                <span class="like-date">
                                                    <i class="far fa-calendar-alt"></i>
                                                    {{ $like->created_at ? date('d M Y h:i A', strtotime($like->created_at)) : '-' }}
                                                </span>
                                                <button type="button" class="btn btn-sm btn-outline-danger ml-2 like-detail-btn" data-id="{{ $like->id }}">
                                                   view
                                                </button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="empty-state">
                                            <i class="fas fa-heart"></i>
                                            <div class="font-weight-bold">No likes found</div>
                                            <div class="small">No one has liked this pin yet.</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- ==================== REPORTS TAB ==================== -->
                        <div class="tab-pane fade" id="reports" role="tabpanel" aria-labelledby="reports-tab">
                            <div class="row">
                                <div class="col-12">
                                    @if($pin->pinReports->count())
                                        <div class="mb-3">
                                            <span class="badge bg-danger" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                <i class="fas fa-flag"></i> Total Reports: {{ $pin->pinReports->count() }}
                                            </span>
                                        </div>

                                        @foreach($pin->pinReports as $report)
                                            @php
                                                $reporterAvatar = asset('assets/images/user-default.jpg');
                                                $reporterName = 'Unknown User';
                                                $reporterEmail = 'No email';
                                                $reportedUserName = 'Unknown User';
                                                $reportedUserEmail = 'No email';
                                                
                                                if($report->reporter) {
                                                    $reporterName = $report->reporter->name ?? 'Unknown User';
                                                    $reporterEmail = $report->reporter->email ?? $report->email ?? 'No email';
                                                    
                                                    if($report->reporter->profile_image) {
                                                        $reporterAvatar = asset('storage/' . $report->reporter->profile_image);
                                                    } elseif($report->reporter->images) {
                                                        $images = json_decode($report->reporter->images, true);
                                                        if(is_array($images) && count($images) > 0) {
                                                            $reporterAvatar = asset('storage/' . $images[0]);
                                                        }
                                                    }
                                                } else {
                                                    $reporterEmail = $report->email ?? 'No email';
                                                }
                                                
                                                if($report->reportedUser) {
                                                    $reportedUserName = $report->reportedUser->name ?? 'Unknown User';
                                                    $reportedUserEmail = $report->reportedUser?->gmail_id ?? $report->reportedUser?->phone_code .''.  $report->reportedUser?->phone_number;
                                                }
                                            @endphp
                                            <div class="report-card-pin">
                                                <div class="report-header">
                                                    <div class="reporter-info">
                                                        <img src="{{ $reporterAvatar }}" class="reporter-avatar" 
                                                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($reporterName) }}&color=667eea&background=f0f0f0&size=40'"
                                                             alt="{{ $reporterName }}">
                                                        <div class="reporter-details">
                                                            <span class="reporter-name">{{ $reporterName }}</span>
                                                            <span class="reporter-email">{{ $reporterEmail }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="report-badges">
                                                        <span class="badge bg-{{ $report->status == 'Pending' ? 'warning' : ($report->status == 'Resolved' ? 'success' : 'secondary') }}">
                                                            {{ $report->status ?? 'Pending' }}
                                                        </span>
                                                        <span class="badge bg-danger">{{ $report->report_type ?? 'General' }}</span>
                                                    </div>
                                                </div>

                                                @if($report->reportedUser)
                                                    <div class="mb-2">
                                                        <span class="text-muted small">
                                                            <i class="fas fa-user-slash"></i> Reported User: 
                                                            <strong>{{ $reportedUserName }}</strong>
                                                            <span class="text-muted">({{ $reportedUserEmail }})</span>
                                                        </span>
                                                    </div>
                                                @endif

                                                <div class="report-reason">
                                                    <div class="report-reason-label">
                                                        <i class="fas fa-comment"></i> Reason:
                                                    </div>
                                                    <div class="report-reason-text">
                                                        {{ $report->reason ?? 'No reason provided' }}
                                                    </div>
                                                </div>

                                               @if(!empty($report->image))
                                                @php
                                                    $reportImage = asset('storage/' . ltrim($report->image, '/'));
                                                @endphp

                                                <div class="mt-3">
                                                    <label class="fw-bold mb-2" style="font-weight: 600; color: #4a5568;">
                                                        <i class="fas fa-camera" style="color: #667eea;"></i> Report Screenshot
                                                    </label>

                                                    <div>
                                                        <a href="{{ $reportImage }}" 
                                                        data-lightbox="report-screenshot-{{ $report->id }}" 
                                                        data-title="Report Screenshot - {{ $report->report_type ?? 'Report' }}"
                                                        class="d-inline-block">
                                                            <img src="{{ $reportImage }}"
                                                                class="img-thumbnail report-screenshot"
                                                                alt="Report Screenshot"
                                                                style="max-width: 200px; max-height: 150px; border-radius: 6px; border: 2px solid #e9ecef; cursor: pointer; object-fit: cover; transition: transform 0.2s;"
                                                                onerror="this.style.display='none'">
                                                        </a>
                                                       
                                                    </div>
                                                </div>
                                            @endif
                                                <div class="report-meta">
                                                    <span class="report-date">
                                                        <i class="far fa-calendar-alt"></i>
                                                        {{ $report->created_at ? date('d M Y h:i A', strtotime($report->created_at)) : '-' }}
                                                    </span>
                                                    <button type="button" class="btn btn-sm btn-outline-danger report-detail-btn-pin" data-id="{{ $report->id }}">
                                                        <i class="fas fa-info-circle"></i> Details
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="empty-state">
                                            <i class="fas fa-flag"></i>
                                            <div class="font-weight-bold">No reports found</div>
                                            <div class="small">No reports have been filed for this pin.</div>
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

<!-- ==================== COMMENT DETAIL MODAL ==================== -->
<div class="modal fade" id="commentDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header comment-detail-modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-comment"></i> Comment Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="commentDetailBody">
                <!-- Comment details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- ==================== LIKE DETAIL MODAL ==================== -->
<div class="modal fade" id="likeDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header like-detail-modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-heart"></i> Like Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="likeDetailBody">
                <!-- Like details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- ==================== REPORT DETAIL MODAL ==================== -->
<div class="modal fade" id="reportDetailModalPin" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header report-detail-modal-header-pin">
                <h5 class="modal-title">
                    <i class="fas fa-flag"></i> Report Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="reportDetailBodyPin">
                <!-- Report details will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    // Any initialization if needed
});

// ==================== COMMENT DETAIL HANDLER ====================
$(document).on('click', '.comment-detail-btn', function () {
    let card = $(this).closest('.comment-card');
    
    // Extract data from card
    let avatar = card.find('.comment-avatar').attr('src');
    let name = card.find('.comment-user-name').text().trim();
    let email = card.find('.comment-user-email').text().trim();
    let comment = card.find('.comment-text').text().trim();
    let date = card.find('.comment-meta span').text().trim();
    let likes = card.find('.badge-secondary').text().trim();
    
    let html = `
        <div class="text-center mb-4">
            <img src="${avatar}" class="detail-avatar mb-3" 
                 onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&color=667eea&background=f0f0f0&size=80'">
            <h5 class="mb-1">${name}</h5>
            <p class="text-muted small">${email}</p>
        </div>
        <div class="detail-item">
            <div class="detail-label"><i class="fas fa-comment"></i> Comment</div>
            <div class="detail-value p-2" style="background: #f8f9fa; border-radius: 6px; border-left: 4px solid #667eea;">
                ${comment}
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-label"><i class="fas fa-heart"></i> Likes</div>
            <div class="detail-value">${likes}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label"><i class="fas fa-calendar-alt"></i> Commented On</div>
            <div class="detail-value">${date}</div>
        </div>
    `;

    $("#commentDetailBody").html(html);
    $("#commentDetailModal").modal('show');
});

// ==================== LIKE DETAIL HANDLER ====================
$(document).on('click', '.like-detail-btn', function () {
    let card = $(this).closest('.like-card');
    
    // Extract data from card
    let avatar = card.find('.like-avatar').attr('src');
    let name = card.find('.like-user-name').text().trim();
    let email = card.find('.like-user-email').text().trim();
    let date = card.find('.like-date').text().trim();
    
    let html = `
        <div class="text-center mb-4">
            <img src="${avatar}" class="detail-avatar mb-3" 
                 onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&color=f5576c&background=f0f0f0&size=80'">
            <h5 class="mb-1">${name}</h5>
            <p class="text-muted small">${email}</p>
        </div>
        <div class="detail-item">
            <div class="detail-label"><i class="fas fa-heart"></i> Like</div>
            <div class="detail-value">
                <span class="badge bg-danger" style="font-size: 1rem;">
                    <i class="fas fa-heart"></i> Liked this pin
                </span>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-label"><i class="fas fa-calendar-alt"></i> Liked On</div>
            <div class="detail-value">${date}</div>
        </div>
    `;

    $("#likeDetailBody").html(html);
    $("#likeDetailModal").modal('show');
});

// ==================== REPORT DETAIL HANDLER ====================
$(document).on('click', '.report-detail-btn-pin', function () {
    let card = $(this).closest('.report-card-pin');
    
    // Extract data from card
    let reporterName = card.find('.reporter-name').text().trim();
    let reporterEmail = card.find('.reporter-email').text().trim();
    let reporterAvatar = card.find('.reporter-avatar').attr('src');
    let status = card.find('.report-badges .badge:first').text().trim();
    let reportType = card.find('.report-badges .badge:last').text().trim();
    let reason = card.find('.report-reason-text').text().trim();
    let date = card.find('.report-date').text().trim();
    let reportedUser = card.find('.mb-2 strong').text().trim();
    let reportedUserEmail = card.find('.mb-2 .text-muted').text().trim().replace(/[()]/g, '');
    
    let statusClass = status.toLowerCase() == 'pending' ? 'warning' : 
                     (status.toLowerCase() == 'resolved' ? 'success' : 'secondary');
    
    let html = `
        <div class="text-center mb-4">
            <span class="badge badge-${statusClass}" 
                  style="font-size: 1rem; padding: 0.5rem 1.5rem;">
                <i class="fas fa-flag"></i> ${status}
            </span>
        </div>
        
        <div class="detail-item">
            <div class="detail-label"><i class="fas fa-user"></i> Reported By</div>
            <div class="detail-value">
                <div class="d-flex align-items-center gap-3">
                    <img src="${reporterAvatar}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" 
                         onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(reporterName)}&color=667eea&background=f0f0f0&size=50'">
                    <div>
                        <div class="font-weight-bold">${reporterName}</div>
                        <div class="text-muted small">${reporterEmail}</div>
                    </div>
                </div>
            </div>
        </div>
        
        ${reportedUser ? `
        <div class="detail-item">
            <div class="detail-label"><i class="fas fa-user-slash"></i> Reported User</div>
            <div class="detail-value">
                <div class="font-weight-bold">${reportedUser}</div>
                <div class="text-muted small">${reportedUserEmail}</div>
            </div>
        </div>
        ` : ''}
        
        <div class="detail-item">
            <div class="detail-label"><i class="fas fa-tag"></i> Report Type</div>
            <div class="detail-value">
                <span class="badge bg-danger">${reportType}</span>
            </div>
        </div>
        
        <div class="detail-item">
            <div class="detail-label"><i class="fas fa-comment"></i> Reason</div>
            <div class="detail-value p-2" style="background: #f8f9fa; border-radius: 6px; border-left: 4px solid #dc3545;">
                ${reason}
            </div>
        </div>
        
        <div class="detail-item">
            <div class="detail-label"><i class="fas fa-calendar-alt"></i> Reported At</div>
            <div class="detail-value">${date}</div>
        </div>
    `;

    $("#reportDetailBodyPin").html(html);
    $("#reportDetailModalPin").modal('show');
});
</script>
@include('admin.include.common-scripts')
@endsection