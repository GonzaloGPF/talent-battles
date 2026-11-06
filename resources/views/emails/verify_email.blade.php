@component('mail::message')
# {{ trans('emails.welcome', ['name' =>  $name ]) }}

{{ trans('emails.welcome_body', ['email' => $email]) }}

@component('mail::button', ['url' => $url])
    {{ trans('labels.verify_email') }}
@endcomponent

{{ trans('emails.email_success_thanks', ['app_name' => config('app.name')]) }}

@endcomponent
