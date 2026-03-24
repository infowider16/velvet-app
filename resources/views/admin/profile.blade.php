@extends('layouts.admin')

@section('title', 'Admin Profile')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/vendors/iconfonts/mdi/css/materialdesignicons.min.css') }}">
<style>
.profile-image-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #007bff;
    margin-bottom: 20px;
}
.image-upload-container {
    position: relative;
    display: inline-block;
}
.image-upload-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: #007bff;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    color: white;
    cursor: pointer;
}
.image-upload-btn:hover {
    background: #0056b3;
}
.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}
.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}
.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Update Profile</h4>
                    <p class="card-description">Update your profile information</p>
                    
                    <form id="profile-form" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="image-upload-container">
                                    <img id="profile-preview" 
                                         src="{{ auth('admin')->user()->profile_image ? asset('storage/' . auth('admin')->user()->profile_image) : asset('assets/images/user-default.jpg') }}" 
                                         alt="Profile Image" 
                                         class="profile-image-preview">
                                    <button type="button" class="image-upload-btn" onclick="$('#profile_image').click();">
                                        <i class="mdi mdi-camera"></i>
                                    </button>
                                </div>
                                <input type="file" id="profile_image" name="profile_image" 
                                       accept="image/*" style="display: none;">
                                <div class="mt-2">
                                    <small class="text-muted">Click camera icon to change profile image</small>
                                </div>
                                <span class="text-danger profile_image_error error-text"></span>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ auth('admin')->user()->name }}" 
                                           placeholder="Enter full name" required>
                                    <span class="text-danger name_error error-text"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ auth('admin')->user()->email }}" 
                                           placeholder="Enter email address" required>
                                    <span class="text-danger email_error error-text"></span>
                                </div>
                                
                               
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary mr-2">
                                        <i class="mdi mdi-content-save"></i> Update Profile
                                    </button>
                                    
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Change Password Section -->
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Change Password</h4>
                    <p class="card-description">Update your password</p>
                    
                    <form id="password-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="current_password_change">Current Password</label>
                                    <input type="password" class="form-control" id="current_password_change" 
                                           name="current_password" placeholder="Enter current password" required>
                                    <span class="text-danger current_password_error_change error-text"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" 
                                           name="new_password" placeholder="Enter new password" required>
                                    <span class="text-danger new_password_error error-text"></span>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="new_password_confirmation" placeholder="Confirm new password" required>
                                    <span class="text-danger new_password_confirmation_error error-text"></span>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary mr-2">
                                        <i class="mdi mdi-lock-reset"></i> Change Password
                                    </button>
                                </div>
                            </div>
                            
                            
                        </div>
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
    // Image preview functionality
    $('#profile_image').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                $('.profile_image_error').text('Please select a valid image file (JPEG, PNG, GIF)');
                return;
            }
            
            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                $('.profile_image_error').text('File size must be less than 5MB');
                return;
            }
            
            $('.profile_image_error').text('');
            
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#profile-preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Profile update form
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let submitBtn = $(this).find('button[type="submit"]');
        
        // Reset errors
        $('.error-text').text('');
        
        // Client-side validation
        let name = $('input[name="name"]').val().trim();
        let email = $('input[name="email"]').val().trim();
        let hasError = false;
        
        if (name === '') {
            $('.name_error').text('Name is required');
            hasError = true;
        }
        
        if (email === '') {
            $('.email_error').text('Email is required');
            hasError = true;
        } else if (!/^\S+@\S+\.\S+$/.test(email)) {
            $('.email_error').text('Enter a valid email address');
            hasError = true;
        }
        
        if (hasError) return;
        
        submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Updating...');
        
        $.ajax({
            url: "{{ route('admin.update') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 1) {
                    toastr.success(response.message || 'Profile updated successfully!');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    toastr.error(response.error || 'Something went wrong!');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, val) {
                        $('.' + key + '_error').text(val[0]);
                    });
                } else {
                    toastr.error('Something went wrong!');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="mdi mdi-content-save"></i> Update Profile');
            }
        });
    });
    
    // Password change form
    $('#password-form').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let submitBtn = $(this).find('button[type="submit"]');
        
        // Reset errors
        $('.error-text').text('');
        
        // Client-side validation
        let newPassword = $('input[name="new_password"]').val();
        let confirmPassword = $('input[name="new_password_confirmation"]').val();
        let hasError = false;
        
        if (newPassword.length < 6) {
            $('.new_password_error').text('Password must be at least 6 characters');
            hasError = true;
        }
        
        if (newPassword !== confirmPassword) {
            $('.new_password_confirmation_error').text('Passwords do not match');
            hasError = true;
        }
        
        if (hasError) return;
        
        submitBtn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Changing...');
        
        $.ajax({
            url: "{{ route('admin.updatePassword') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 1) {
                    toastr.success(response.message || 'Password changed successfully!');
                    $('#password-form')[0].reset();
                } else {
                    toastr.error(response.error || 'Something went wrong!');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, val) {
                        $('.' + key + '_error_change').text(val[0]);
                    });
                } else {
                    toastr.error('Something went wrong!');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="mdi mdi-lock-reset"></i> Change Password');
            }
        });
    });
});
</script>
@endsection
