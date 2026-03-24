@extends('layouts.master-login')

@section('title', 'Admin Forgot Password')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/vendors/iconfonts/mdi/css/materialdesignicons.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendors/iconfonts/ionicons/dist/css/ionicons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendors/iconfonts/flag-icon-css/css/flag-icon.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.addons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/shared/style.css') }}">
@endsection

@section('body-class', 'container-scroller')

@section('content')
<div class="container-fluid page-body-wrapper full-page-wrapper">
  <div class="content-wrapper d-flex align-items-center auth auth-bg-1 theme-one">
    <div class="row w-100">
      <div class="col-lg-4 mx-auto">
        <div class="auto-form-wrapper">
          <form method="POST" action="{{ route('admin.sendForgotPasswordEmail') }}" class="forgot-password-form" novalidate="">
            @csrf
            <h3 class="text-center mb-4">Forgot Password?</h3>
            <p class="text-center mb-4 text-muted">Enter your email address and we'll send you a new password.</p>
            
            <div class="form-group">
              <label class="label">Email</label>
              <div class="input-group">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                  placeholder="Enter your email address" value="{{ old('email') }}" required>
                <div class="input-group-append">
                  <span class="input-group-text">
                    <i class="mdi mdi-email-outline"></i>
                  </span>
                </div>
              </div>
              <span class="text-danger email_error error-text">@error('email'){{ $message }}@enderror</span>
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary submit-btn btn-block">Send Reset Password</button>
            </div>

            <div class="form-group text-center">
              <a href="{{ route('admin.login') }}" class="text-small text-black">Back to Login</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('assets/vendors/js/vendor.bundle.addons.js') }}"></script>
<script src="{{ asset('assets/js/shared/off-canvas.js') }}"></script>
<script src="{{ asset('assets/js/shared/misc.js') }}"></script>
<script src="{{ asset('assets/js/shared/jquery.cookie.js') }}"></script>
<script>
  $(document).ready(function() {
    $(document).on('submit', '.forgot-password-form', function(e) {
      e.preventDefault();

      let email = $('input[name="email"]').val().trim();
      let hasError = false;

      // Reset old errors
      $('.error-text').text('');

      // Client-side validation
      if (email === '') {
        $('.email_error').text('Email is required');
        hasError = true;
      } else if (!/^\S+@\S+\.\S+$/.test(email)) {
        $('.email_error').text('Enter a valid email address');
        hasError = true;
      }

      if (hasError) return;

      let submitBtn = $(this).find('button[type="submit"]');
      submitBtn.prop('disabled', true).text('Sending...');

      let form = $(this);
      let url = form.attr('action');
      let data = form.serialize();

      $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function(response) {
          submitBtn.prop('disabled', false).text('Send Reset Password');
          
          if (response.status === 200) {
            alert('Password reset email sent successfully! Please check your email.');
            window.location.href = "{{ route('admin.login') }}";
          } else {
            alert(response.message || 'Something went wrong!');
          }
        },
        error: function(xhr) {
          submitBtn.prop('disabled', false).text('Send Reset Password');

          if (xhr.status === 422) {
            let errors = xhr.responseJSON.errors;
            $.each(errors, function(key, val) {
              $('.' + key + '_error').text(val[0]);
            });
          } else if (xhr.status === 404) {
            $('.email_error').text('Email address not found in our records.');
          } else {
            let response = xhr.responseJSON;
            alert(response.message || 'Something went wrong!');
          }
        }
      });
    });
  });
</script>
@endsection
