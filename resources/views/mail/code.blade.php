@php
    use Carbon\Carbon;
    $expiredAt = $ttl->format('d.m.Y H:i:s');
@endphp

@component('mail::message')
# Hello!

Your confirmation code is: <strong>{{ $code }}</strong>

This code will expire in 15 minutes (before {{ $expiredAt }} UTC)).

Thanks,<br>
{{ config('app.name') }}
@endcomponent
