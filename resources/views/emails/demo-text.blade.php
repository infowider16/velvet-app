{{ config('app.name', 'Velvet Admin') }} - Password Reset

{!! strip_tags($mailData['body'] ?? 'Password reset email content.') !!}

@if(isset($mailData['password']))
Your New Temporary Password: {{ $mailData['password'] }}
@endif

Please change this temporary password after logging in.
Login URL: {{ route('admin.login') }}

---
This email was sent from {{ config('app.name', 'Velvet Admin') }}
If you did not request this password reset, please contact support immediately.
