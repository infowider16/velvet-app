<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $mailData['subject'] ?? 'Email' }}</title>

    <style>

        body {

            font-family: Arial, sans-serif;

            line-height: 1.6;

            color: #333;

            max-width: 600px;

            margin: 0 auto;

            padding: 20px;

            background-color: #f4f4f4;

        }

        .email-container {

            background-color: #ffffff;

            border-radius: 10px;

            overflow: hidden;

            box-shadow: 0 0 10px rgba(0,0,0,0.1);

        }

        .header {

            background-color: #007bff;

            color: white;

            padding: 20px;

            text-align: center;

        }

        .content {

            padding: 30px;

        }

        .footer {

            background-color: #f8f9fa;

            padding: 20px;

            text-align: center;

            font-size: 12px;

            color: #6c757d;

        }

        .password-box {

            background-color: #e3f2fd;

            border: 2px solid #2196f3;

            padding: 15px;

            margin: 20px 0;

            border-radius: 5px;

            text-align: center;

            font-size: 20px;

            font-weight: bold;

            color: #1976d2;

        }

        .btn {

            display: inline-block;

            padding: 12px 30px;

            background-color: #007bff;

            color: white;

            text-decoration: none;

            border-radius: 5px;

            margin: 15px 0;

        }

    </style>

</head>

<body>

    <div class="email-container">

        <div class="header">

            <h2>{{ config('app.name', 'Velvet Admin') }}</h2>

            <p>{{ __('message.password_reset_request') }}</p>

        </div>

        

        <div class="content">

            {!! $mailData['body'] ?? __('message.password_reset_email_content') !!}
            

            @if(isset($mailData['password']))

            <div class="password-box">

               {{ __('message.temporary_password_label') }} <br>

                <strong>{{ $mailData['password'] }}</strong>

            </div>

            <p><strong>{{ __('message.temporary_password_note') }}</strong></p>

            @endif



            <p>

                <a href="{{ route('admin.login') }}" class="btn">  {{ __('message.login_admin_panel') }}</a>

            </p>

        </div>

        

        <div class="footer">

            <p>
                {{ __('message.email_sent_from', ['app' => config('app.name', 'Velvet Admin')]) }}
            </p>

            <p>{{ __('message.password_reset_not_requested') }}</p>

            <p>
                &copy; {{ date('Y') }} {{ config('app.name', 'Velvet Admin') }}.
                {{ __('message.all_rights_reserved') }}
            </p>

        </div>

    </div>

</body>

</html>

