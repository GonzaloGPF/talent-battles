@component('mail::message')
@if(isset($userName))
# {{ trans('emails.hello_name', ['name' => $userName]) }}
@endif

@if(empty($isNew))
{{ trans('emails.reset_password_body') }}
@else
{{ trans('emails.reset_password_body_new', ['app_name' => config('app.name')]) }}
@endif

@component('mail::button', ['url' => $url])
    {{ trans('labels.set_password') }}
@endcomponent

{{ trans('emails.email_success') }}

@endcomponent
