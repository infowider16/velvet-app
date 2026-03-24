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
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    margin: 4px;
    border: 2px solid #eee;
    transition: border-color 0.2s;
}
.user-gallery-image:hover {
    border-color: #007bff;
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
                    <h4 class="card-title">User Details</h4>
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
                                </div>
                                <div class="col-md-8">
                                    <table class="table table-borderless">
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
                            <div class="row">
                                <div class="col-12 text-center">
                                    <h6>User Images</h6>
                                    @php
                                        $images = [];
                                        if (!empty($user->images)) {
                                            $images = is_array($user->images) ? $user->images : json_decode($user->images, true);
                                        }
                                    @endphp
                                    @if(!empty($images) && count($images))
                                        @foreach($images as $img)
                                            @php
                                                $imgUrl = Str::startsWith($img, ['http://', 'https://', '/'])
                                                    ? asset($img)
                                                    : asset('storage/' . $img);
                                            @endphp
                                            <a href="{{ $imgUrl }}" data-lightbox="user-gallery" data-title="{{ $user->name }}">
                                                <img src="{{ $imgUrl }}" class="user-gallery-image" alt="User Image">
                                            </a>
                                        @endforeach
                                    @else
                                        <div class="text-muted">No images uploaded</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mt-3">Back to List</a>
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
</script>
@endsection






