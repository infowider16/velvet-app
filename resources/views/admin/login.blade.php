@extends('layouts.master-login')



@section('title', 'Admin Login')



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

          <form method="POST" action="{{ route('admin.adminlogin') }}" class="" novalidate="">

            @csrf

            <div class="form-group">

              <label class="label">Email</label>

              <div class="input-group">

                <input type="text" name="email" class="form-control @error('email') is-invalid @enderror"

                  placeholder="Email" value="{{ old('email') }}" required>

                <div class="input-group-append">

                  <span class="input-group-text">

                    <i class="mdi mdi-check-circle-outline"></i>

                  </span>

                </div>

              </div>

              <span class="text-danger email_error error-text">@error('email'){{ $message }}@enderror</span>



            </div>

            <div class="form-group">

              <label class="label">Password</label>

              <div class="input-group">

                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"

                  placeholder="*********" required>

                <div class="input-group-append">

                  <span class="input-group-text">

                    <i class="mdi mdi-check-circle-outline"></i>

                  </span>

                </div>

              </div>

              <span class="text-danger password password_error error-text">@error('password'){{ $message }}@enderror</span>

            </div>

            <div class="form-group">

              <button type="submit" class="btn btn-primary submit-btn btn-block">Login</button>

            </div>

            <div class="form-group d-flex justify-content-between">

              <a href="{{ route('admin.forgotpassword') }}" class="text-small forgot-password text-black">Forgot Password</a>

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



    $(document).on('submit', 'form[action="{{ route("admin.adminlogin") }}"]', function(e) {

      e.preventDefault();



      // Disable button on submit



      let email = $('input[name="email"]').val().trim();

      let password = $('input[name="password"]').val().trim();

      let hasError = false;



      // Reset old errors

      $('.error-text').text('');



      // Client-side checks

      if (email === '') {

        $('.email_error').text('Email is required');

        hasError = true;

      } else if (!/^\S+@\S+\.\S+$/.test(email)) {

        $('.email_error').text('Enter a valid email address');

        hasError = true;

      }



      if (password === '') {

        $('.password_error').text('Password is required');

        hasError = true;

      } else if (password.length < 6) {

        $('.password_error').text('Password must be at least 6 characters');

        hasError = true;

      }



      if (hasError) return; // Stop if errors



      let submitBtn = $(this).find('button[type="submit"]');

      submitBtn.prop('disabled', true).text('Please wait...');



      // Clear old errors

      $('.error-text').text('');



      let form = $(this);

      let url = form.attr('action');

      let data = form.serialize();



      $.ajax({

        url: url,

        type: 'POST',

        data: data,

        success: function(response) {

          console.log(response);

          if (response.status === 1 && response.data && response.data.redirect) {

            window.location.href = response.data.redirect;

          } 

          else if (response.data && response.data.redirect) {

            window.location.href = response.data.redirect;

          }

          else {

            $('.email_error').text(response.message || "Login failed. Please try again.");

          }

        },

        error: function(xhr) {

          submitBtn.prop('disabled', false).text('Login'); // re-enable button



          // Always clear previous errors

          $('.error-text').text('');



          if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {

            let errors = xhr.responseJSON.errors;

            $.each(errors, function(key, val) {

              $('.' + key + '_error').text(val[0]);

            });

          } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.email) {

            // For errorResponse with data.email

            $('.email_error').text(xhr.responseJSON.data.email[0]);

          } else if (xhr.responseJSON && xhr.responseJSON.message) {

            // For errorResponse with only message

            $('.email_error').text(xhr.responseJSON.message);

          } else {

            $('.email_error').text("Something went wrong. Please try again.");

          }

        }

      });

    });



  });

</script>

@endsection