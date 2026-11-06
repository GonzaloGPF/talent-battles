@component('mail::message')
# {{ trans('emails.contact_email_header', ['email' =>  $email ]) }}

{{ $message }}

@component('mail::button', ['url' => "mailto:$email"])
    {{ trans('actions.answer') }}
@endcomponent

@endcomponent
