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
            background-color: #1c45ef;
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

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #1c45ef;
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

            <p>
                {{ $mailData['header'] ?? 'Contact Us Enquiry Submission' }}
            </p>
        </div>

        <div class="content">

            {!! $mailData['body'] ?? '' !!}

        </div>

        <div class="footer" style="
            background-color: #1c45ef;
            padding: 18px;
            text-align: center;
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
        ">

            Copyright © {{ date('Y') }}
            {{ config('app.name', 'Velvet Admin') }}.
            All rights reserved.

        </div>
    </div>

</body>
</html>