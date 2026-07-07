@extends('layouts.admin')

@section('title', 'Group Detail')

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

.badge-public {
    background: #28a745;
    color: #fff;
    padding: 6px 12px;
    font-size: 0.85rem;
}

.badge-private {
    background: #dc3545;
    color: #fff;
    padding: 6px 12px;
    font-size: 0.85rem;
}

.group-gallery-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.group-gallery-image:hover {
    transform: scale(1.02);
}

.group-media-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: .85rem;
}

.group-media-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 1rem 1.5rem rgba(0, 0, 0, 0.12);
}

.group-media-empty {
    border: 2px dashed #dee2e6;
}

.member-avatar-sm {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

/* Report Card Styles */
.report-card {
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.report-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    background-color: #fff;
    border-color: #dc3545;
}

.report-card .report-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.report-card .reporter-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.report-card .reporter-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.report-card .reporter-details {
    display: flex;
    flex-direction: column;
}

.report-card .reporter-name {
    font-weight: 600;
    font-size: 0.95rem;
    color: #2d3748;
}

.report-card .reporter-email {
    font-size: 0.8rem;
    color: #718096;
}

.report-card .report-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.report-card .report-reason {
    margin-top: 0.75rem;
    padding: 0.75rem 1rem;
    background: #fff;
    border-radius: 6px;
    border-left: 4px solid #dc3545;
}

.report-card .report-reason-label {
    font-weight: 600;
    font-size: 0.85rem;
    color: #4a5568;
}

.report-card .report-reason-text {
    margin-top: 4px;
    color: #2d3748;
    font-size: 0.95rem;
}

.report-card .report-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

.report-card .report-date {
    font-size: 0.85rem;
    color: #718096;
}

/* Member Detail Modal */
.member-detail-modal-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px 8px 0 0;
    border: none;
}

#memberDetailModal .modal-content {
    border-radius: 1rem;
    overflow: hidden;
    max-width: 420px;
    width: 100%;
    margin: 0 auto;
}

#memberDetailModal .modal-dialog {
    max-width: 420px;
    width: 100%;
    margin: 1.5rem auto;
}

.member-detail-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #667eea;
}

.member-detail-item {
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e9ecef;
}

.member-detail-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.member-detail-label {
    font-weight: 600;
    color: #495057;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.member-detail-value {
    color: #212529;
    font-size: 0.95rem;
    font-weight: 500;
    word-break: break-word;
}

/* Report Detail Modal */
.report-detail-modal-header {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px 8px 0 0;
    border: none;
}

#reportDetailModal .modal-content {
    border-radius: 1rem;
    overflow: hidden;
    max-width: 500px;
    width: 100%;
    margin: 0 auto;
}

#reportDetailModal .modal-dialog {
    max-width: 500px;
    width: 100%;
    margin: 1.5rem auto;
}

.report-detail-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.5);
}

.report-detail-item {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.report-detail-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.report-detail-label {
    font-weight: 600;
    color: #495057;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.report-detail-value {
    color: #212529;
    font-size: 0.95rem;
}

.report-detail-reason-box {
    background: #f8f9fa;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    border-left: 4px solid #dc3545;
}

/* DataTable custom styling to match UI */
#members-table_wrapper .dataTables_length {
    float: left;
    margin-bottom: 1rem;
}

#members-table_wrapper .dataTables_filter {
    float: right;
    margin-bottom: 1rem;
}

#members-table_wrapper .dataTables_info {
    float: left;
    padding-top: 0.755em;
}

#members-table_wrapper .dataTables_paginate {
    float: right;
    padding-top: 0.25em;
}

#members-table_wrapper .dataTables_paginate .paginate_button {
    padding: 0.5em 1em;
    margin: 0 0.2em;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    color: #333;
    background: #fff;
}

#members-table_wrapper .dataTables_paginate .paginate_button.current {
    background: #007bff;
    color: #fff !important;
    border-color: #007bff;
}

#members-table_wrapper .dataTables_paginate .paginate_button:hover {
    background: #e9ecef;
    border-color: #dee2e6;
    color: #333 !important;
}

#members-table_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #0069d9;
    border-color: #0069d9;
    color: #fff !important;
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
                        <h4 class="card-title mb-0">Group Details</h4>
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
                    <ul class="nav nav-tabs mb-4" id="groupTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" role="tab" aria-controls="basic" aria-selected="true">
                                Basic Details
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="members-tab" data-toggle="tab" href="#members" role="tab" aria-controls="members" aria-selected="false">
                                Members ({{ $group->members->count() }})
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="media-tab" data-toggle="tab" href="#media" role="tab" aria-controls="media" aria-selected="false">
                                Group Media ({{ $group->media->count() }})
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="reports-tab" data-toggle="tab" href="#reports" role="tab" aria-controls="reports" aria-selected="false">
                                Group Reports ({{ $group->reports->count() }})
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="groupTabContent">
                        <!-- ==================== BASIC DETAILS TAB ==================== -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                            <div class="row">
                                <!-- Left Column - Group Image -->
                                <div class="col-md-4 text-center">
                                    @php
                                        $groupImage = asset('assets/images/no-image.png');
                                        if(!empty($group->image)) {
                                            $groupImage = asset('storage/' . $group->image);
                                        }
                                    @endphp
                                    <img src="{{ $groupImage }}" class="profile-image-preview" alt="{{ $group->name }}">
                                    <h4 class="mt-3 font-weight-bold">{{ $group->name }}</h4>
                                    <p class="text-muted">{{ $group->description ?: 'No description provided' }}</p>
                                    <div class="mt-2">
                                        @if($group->group_type == 0)
                                            <span class="badge badge-public">Public</span>
                                        @else
                                            <span class="badge badge-private">Private</span>
                                        @endif
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt"></i> Created: {{ date('d M Y', strtotime($group->created_at)) }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Right Column - Group Details Table -->
                                <div class="col-md-8">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 200px;">Group Name</th>
                                            <td>{{ $group->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td>{{ $group->description ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Group Type</th>
                                            <td>
                                                @if($group->group_type == 0)
                                                    <span class="badge badge-public">Public</span>
                                                @else
                                                    <span class="badge badge-private">Private</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Notification</th>
                                            <td>
                                                <span class="badge {{ $group->notification_status ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $group->notification_status ? 'On' : 'Off' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Member Permission</th>
                                            <td>
                                                <span class="badge {{ $group->is_member_permission ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $group->is_member_permission ? 'Allowed' : 'Not Allowed' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Total Members</th>
                                            <td>{{ $group->members->count() }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Reports</th>
                                            <td>{{ $group->reports->count() }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created By</th>
                                            <td>
                                                @if($group->creator)
                                                    <div class="d-flex align-items-center gap-2">
                                                        @php
                                                            $creatorAvatar = asset('assets/images/user-default.jpg');
                                                            if($group->creator->profile_image) {
                                                                $creatorAvatar = asset('storage/' . $group->creator->profile_image);
                                                            } elseif($group->creator->images) {
                                                                $images = json_decode($group->creator->images, true);
                                                                if(is_array($images) && count($images) > 0) {
                                                                    $creatorAvatar = asset('storage/' . $images[0]);
                                                                }
                                                            }
                                                        @endphp
                                                        <img src="{{ $creatorAvatar }}" 
                                                             style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;" 
                                                             alt="{{ $group->creator->name }}">
                                                        <span class="m-2">{{ $group->creator->name }}</span>
                                                    </div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Owner Email</th>
                                            <td>{{ optional($group->creator)->email ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Owner Phone</th>
                                            <td>
                                                @if($group->creator)
                                                    {{ $group->creator->phone_code ? '+' . $group->creator->phone_code . ' ' : '' }}
                                                    {{ $group->creator->phone_number ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ $group->created_at ? date('d M Y h:i A', strtotime($group->created_at)) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Updated At</th>
                                            <td>{{ $group->updated_at ? date('d M Y h:i A', strtotime($group->updated_at)) : '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== MEMBERS TAB ==================== -->
                        <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="members-tab">
                            <div class="row">
                                <div class="col-12">
                                    @if($group->members->count())
                                        <div class="table-responsive">
                                            <table class="table table-responsive table-bordered table-striped" id="members-table">
                                                <thead>
                                                    <tr>
                                                        <th>Member</th>
                                                        <th>Role</th>
                                                        <th>Permission</th>
                                                        <th>Status</th>
                                                        <th>Unread</th>
                                                        <th>Joined At</th>
                                                        <th>Group Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($group->members as $member)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    @php
                                                                        $avatar = asset('assets/images/user-default.jpg');
                                                                        if($member->user) {
                                                                            if($member->user->profile_image) {
                                                                                $avatar = asset('storage/' . $member->user->profile_image);
                                                                            } elseif($member->user->images) {
                                                                                $images = json_decode($member->user->images, true);
                                                                                if(is_array($images) && count($images) > 0) {
                                                                                    $avatar = asset('storage/' . $images[0]);
                                                                                }
                                                                            }
                                                                        }
                                                                        $memberName = $member->user->name ?? 'Unknown User';
                                                                        $memberEmail = $member->user->email ?? '';
                                                                        $shortName = '';
                                                                        $nameParts = explode(' ', $memberName);
                                                                        foreach($nameParts as $part) {
                                                                            if(strlen($part) > 0) {
                                                                                $shortName .= strtoupper($part[0]);
                                                                            }
                                                                        }
                                                                        $shortName = substr($shortName, 0, 2);
                                                                    @endphp
                                                                    <img src="{{ $avatar }}" class="member-avatar-sm" 
                                                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($memberName) }}&color=667eea&background=f0f0f0&size=40'"
                                                                         alt="{{ $memberName }}">
                                                                    <div>
                                                                        <div class="font-weight-bold m-2">{{ $memberName }}</div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge {{ $member->role == 'admin' ? 'bg-primary' : 'bg-secondary' }}">
                                                                    {{ ucfirst($member->role) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge {{ $member->is_member_permission ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ $member->is_member_permission ? 'Allowed' : 'Not Allowed' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $statusLabels = [
                                                                        0 => ['label' => 'Active', 'class' => 'bg-success'],
                                                                        1 => ['label' => 'Blocked', 'class' => 'bg-danger'],
                                                                        2 => ['label' => 'Left', 'class' => 'bg-warning']
                                                                    ];
                                                                    $status = $statusLabels[$member->status] ?? ['label' => 'Unknown', 'class' => 'bg-secondary'];
                                                                @endphp
                                                                <span class="badge {{ $status['class'] }}">
                                                                    {{ $status['label'] }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $member->unread_count ?? 0 }}</td>
                                                            <td>{{ $member->created_at ? date('d M Y', strtotime($member->created_at)) : '-' }}</td>
                                                            <td>
                                                                <span class="badge bg-info">
                                                                    {{ $member->group_status ?? '-' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-info member-detail-btn" data-id="{{ $member->id }}">
                                                                    <i class="fas fa-eye"></i> View
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-5 border rounded group-media-empty">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <p class="mb-1 font-weight-bold">No members found</p>
                                            <p class="mb-0 small">This group has no members yet.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- ==================== GROUP MEDIA TAB ==================== -->
                        <div class="tab-pane fade" id="media" role="tabpanel" aria-labelledby="media-tab">
                            <div class="row">
                                <div class="col-12">
                                    @if($group->media->count())
                                        <div class="row gx-2 gy-3">
                                            @foreach($group->media as $item)
                                                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                                    <div class="card group-media-card border-0 shadow-sm overflow-hidden">
                                                        @php
                                                            $mediaUrl = asset('storage/' . $item->file_path);
                                                            $isImage = in_array($item->file_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg']);
                                                            $fileName = basename($item->file_path);
                                                        @endphp

                                                        @if($isImage)
                                                            <a href="{{ $mediaUrl }}" data-lightbox="group-media-gallery" data-title="{{ $fileName }}">
                                                                <img src="{{ $mediaUrl }}" class="group-gallery-image" alt="{{ $fileName }}">
                                                            </a>
                                                        @else
                                                            <div class="p-4 text-center" style="height: 180px; display: flex; flex-direction: column; justify-content: center; align-items: center; background: #f8f9fa;">
                                                                <i class="fas fa-file-alt fa-3x text-primary mb-2"></i>
                                                                <div class="text-truncate" style="max-width: 100%; font-size: 0.85rem;">
                                                                    {{ $fileName }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class="p-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                                            <span class="text-truncate" style="max-width: 150px;">{{ $fileName }}</span>
                                                            <span class="badge bg-secondary">{{ strtoupper(pathinfo($fileName, PATHINFO_EXTENSION)) }}</span>
                                                        </div>
                                                        <div class="p-2 border-top">
                                                            <a href="{{ $mediaUrl }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-5 border rounded group-media-empty">
                                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                                            <p class="mb-1 font-weight-bold">No group media found</p>
                                            <p class="mb-0 small">Media shared in this group will appear here.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- ==================== GROUP REPORTS TAB ==================== -->
                        <div class="tab-pane fade" id="reports" role="tabpanel" aria-labelledby="reports-tab">
                            <div class="row">
                                <div class="col-12">
                                    @if($group->reports->count())
                                        <div class="mb-3">
                                            <span class="badge bg-danger" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                <i class="fas fa-flag"></i> Total Reports: {{ $group->reports->count() }}
                                            </span>
                                        </div>

                                        @foreach($group->reports as $report)
                                            <div class="report-card">
                                                <div class="report-header">
                                                    <div class="reporter-info">
                                                        @php
                                                            $reporterImage = asset('assets/images/user-default.jpg');
                                                            $reporterName = 'Unknown User';
                                                            $reporterEmail = 'No email';
                                                            
                                                            if($report->reporter) {
                                                                $reporterName = $report->reporter->name ?? 'Unknown User';
                                                                $reporterEmail = $report->reporter->email ?? $report->email ?? 'No email';
                                                                
                                                                if($report->reporter->profile_image) {
                                                                    $reporterImage = asset('storage/' . $report->reporter->profile_image);
                                                                } elseif($report->reporter->images) {
                                                                    $images = json_decode($report->reporter->images, true);
                                                                    if(is_array($images) && count($images) > 0) {
                                                                        $reporterImage = asset('storage/' . $images[0]);
                                                                    }
                                                                }
                                                            } else {
                                                                $reporterEmail = $report->email ?? 'No email';
                                                            }
                                                            
                                                            $initials = '';
                                                            $nameParts = explode(' ', $reporterName);
                                                            foreach($nameParts as $part) {
                                                                if(strlen($part) > 0) {
                                                                    $initials .= strtoupper($part[0]);
                                                                }
                                                            }
                                                            $initials = substr($initials, 0, 2);
                                                        @endphp
                                                        <img src="{{ $reporterImage }}" class="reporter-avatar" 
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
                                                    <button type="button" class="btn btn-sm btn-outline-danger report-detail-btn" data-id="{{ $report->id }}">
                                                        <i class="fas fa-info-circle"></i> Details
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center text-muted py-5 border rounded group-media-empty">
                                            <i class="fas fa-flag fa-3x mb-3"></i>
                                            <p class="mb-1 font-weight-bold">No reports found</p>
                                            <p class="mb-0 small">Reported issues for this group will appear here.</p>
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

<!-- ==================== MEMBER DETAIL MODAL ==================== -->
<div class="modal fade" id="memberDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header member-detail-modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user"></i> Member Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="memberDetailBody">
                <!-- Member details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- ==================== REPORT DETAIL MODAL ==================== -->
<div class="modal fade" id="reportDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header report-detail-modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-flag"></i> Report Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="reportDetailBody">
                <!-- Report details will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    // Initialize DataTable for members with proper UI matching your screenshot
    @if($group->members->count())
        $('#members-table').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                emptyTable: "No members found for this group",
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    previous: "Previous",
                    next: "Next"
                }
            },
            columnDefs: [
                { orderable: false, targets: 0 }, // Member column
                { orderable: false, targets: 7 }  // Action column
            ],
            order: [[4, 'asc']] // Sort by Unread count by default
        });
    @endif
});

// ==================== MEMBER DETAIL HANDLER ====================
$(document).on('click', '.member-detail-btn', function () {
    let row = $(this).closest('tr');
    
    // Extract data from row
    let avatar = row.find('td:eq(0) img').attr('src');
    let name = row.find('td:eq(0) .font-weight-bold').text().trim();
    let role = row.find('td:eq(1) .badge').text().trim();
    let permission = row.find('td:eq(2) .badge').text().trim();
    let status = row.find('td:eq(3) .badge').text().trim();
    let unread = row.find('td:eq(4)').text().trim();
    let joined = row.find('td:eq(5)').text().trim();
    let groupStatus = row.find('td:eq(6) .badge').text().trim();
    
    let statusClass = status.toLowerCase() == 'active' ? 'text-success' : 
                     (status.toLowerCase() == 'blocked' ? 'text-danger' : 'text-warning');
    let permissionClass = permission.toLowerCase() == 'allowed' ? 'text-success' : 'text-danger';
    
    let html = `
        <div class="text-center mb-4">
            <img src="${avatar}" class="member-detail-avatar mb-3" 
                 onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&color=667eea&background=f0f0f0&size=100'">
            <h5 class="mb-1">${name}</h5>
        </div>
        <div class="member-detail-item">
            <div class="member-detail-label"><i class="fas fa-user-tag"></i> Role</div>
            <div class="member-detail-value">${role}</div>
        </div>
        <div class="member-detail-item">
            <div class="member-detail-label"><i class="fas fa-user-check"></i> Permission</div>
            <div class="member-detail-value ${permissionClass}">${permission}</div>
        </div>
        <div class="member-detail-item">
            <div class="member-detail-label"><i class="fas fa-circle"></i> Status</div>
            <div class="member-detail-value ${statusClass}">${status}</div>
        </div>
        <div class="member-detail-item">
            <div class="member-detail-label"><i class="fas fa-envelope"></i> Unread Count</div>
            <div class="member-detail-value">${unread}</div>
        </div>
        <div class="member-detail-item">
            <div class="member-detail-label"><i class="fas fa-calendar-plus"></i> Joined At</div>
            <div class="member-detail-value">${joined}</div>
        </div>
        <div class="member-detail-item">
            <div class="member-detail-label"><i class="fas fa-users"></i> Group Status</div>
            <div class="member-detail-value">${groupStatus}</div>
        </div>
    `;

    $("#memberDetailBody").html(html);
    $("#memberDetailModal").modal('show');
});

// ==================== REPORT DETAIL HANDLER ====================
$(document).on('click', '.report-detail-btn', function () {
    let card = $(this).closest('.report-card');
    
    // Extract data from card
    let reporterName = card.find('.reporter-name').text().trim();
    let reporterEmail = card.find('.reporter-email').text().trim();
    let reporterAvatar = card.find('.reporter-avatar').attr('src');
    let status = card.find('.report-badges .badge:first').text().trim();
    let reportType = card.find('.report-badges .badge:last').text().trim();
    let reason = card.find('.report-reason-text').text().trim();
    let date = card.find('.report-date').text().trim();
    
    let statusClass = status.toLowerCase() == 'pending' ? 'warning' : 
                     (status.toLowerCase() == 'resolved' ? 'success' : 'secondary');
    
    let html = `
        <div class="text-center mb-4">
            <span class="badge badge-${statusClass}" 
                  style="font-size: 1rem; padding: 0.5rem 1.5rem;">
                <i class="fas fa-flag"></i> ${status}
            </span>
        </div>
        
        <div class="report-detail-item">
            <div class="report-detail-label"><i class="fas fa-user"></i> Reported By</div>
            <div class="report-detail-value">
                <div class="d-flex align-items-center gap-3">
                    <img src="${reporterAvatar}" class="report-detail-avatar" 
                         onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(reporterName)}&color=667eea&background=f0f0f0&size=60'">
                    <div>
                        <div class="font-weight-bold">${reporterName}</div>
                        <div class="text-muted small">${reporterEmail}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="report-detail-item">
            <div class="report-detail-label"><i class="fas fa-tag"></i> Report Type</div>
            <div class="report-detail-value">
                <span class="badge bg-danger">${reportType}</span>
            </div>
        </div>
        
        <div class="report-detail-item">
            <div class="report-detail-label"><i class="fas fa-comment"></i> Reason</div>
            <div class="report-detail-reason-box">
                ${reason}
            </div>
        </div>
        
        <div class="report-detail-item">
            <div class="report-detail-label"><i class="fas fa-calendar-alt"></i> Reported At</div>
            <div class="report-detail-value">${date}</div>
        </div>
    `;

    $("#reportDetailBody").html(html);
    $("#reportDetailModal").modal('show');
});
</script>
@include('admin.include.common-scripts')
@endsection