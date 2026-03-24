<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    

    <title>@yield('title', 'Admin Login') - {{ config('app.name', 'Laravel') }}</title>

    

    <!-- Core CSS -->

    <link rel="stylesheet" href="{{ asset('assets/vendors/iconfonts/mdi/css/materialdesignicons.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/shared/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/demo_1/style.css') }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />

    

    <!-- Custom Login Styles -->

    <style>

        .auth-bg-custom {

            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

            min-height: 100vh;

        }

        .login-card {

            background: rgba(255, 255, 255, 0.95);

            border-radius: 15px;

            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);

            backdrop-filter: blur(10px);

            padding: 2rem;

        }

        .login-header {

            text-align: center;

            margin-bottom: 2rem;

        }

        .login-header h3 {

            color: #333;

            font-weight: 600;

            margin-bottom: 0.5rem;

        }

        .login-header p {

            color: #666;

            font-size: 14px;

        }

        .form-control:focus {

            border-color: #667eea;

            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);

        }

        .btn-login {

            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

            border: none;

            border-radius: 8px;

            padding: 12px;

            font-weight: 600;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            transition: all 0.3s ease;

        }

        .btn-login:hover {

            transform: translateY(-2px);

            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);

        }

        .error-message {

            font-size: 12px;

            margin-top: 5px;

            display: block;

        }

        .input-group-text {

            background: #f8f9fa;

            border-color: #ced4da;

        }

    </style>

    

    @yield('styles')

    @stack('styles')

</head>

<body class="auth-bg-custom">

    @yield('content')

    

    <!-- Core JS -->
         <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>

    <script src="{{ asset('assets/js/shared/misc.js') }}"></script>

    

    @yield('scripts')

    @stack('scripts')

</body>

</html>

