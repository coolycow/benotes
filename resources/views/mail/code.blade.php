@component('mail::message')
# Hello!

Your confirmation code is: <strong>{{ $code }}</strong>

This code will expire in 15 minutes.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
