@component('mail::message')
# {{ trans('emails.invitation_email_header', [
    'name' =>  $invitation->name ?? $invitation->email,
    'creator' => $invitation->createdBy->name,
    'app_name' => config('app.name')
]) }}

{{ trans('emails.invitation_email_body') }}

@component('mail::button', ['url' => $url])
    {{ tAction('create') }} {{ tl('account') }}
@endcomponent

@endcomponent
