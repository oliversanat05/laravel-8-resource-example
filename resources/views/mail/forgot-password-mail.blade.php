@component('mail::message')
# Hello!

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => env('FRONTEND_APP_URL'). 'auth/change-password/'.$token])
    Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

# Regards,
{{ config('app.name') }}
@endcomponent
