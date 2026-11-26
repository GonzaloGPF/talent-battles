@component('mail::message')

{{ trans('emails.order_success_body') }}

@if($hasUrl)
@component('mail::panel')
{{ trans('emails.order_success_body_2') }}
@endcomponent
@endif

@foreach($lines as $line)
@if(!empty($line['url']))
 - [{{$line['description']}}]({{$line['url']}})
@else
- {{$line['description']}}
@endif
@endforeach

@if($immediateDelivery)
{{ trans('emails.order_success_immediate_delivery') }}

@endif
@if($termsUrl)
{{ trans('emails.order_success_terms', ['url' => $termsUrl]) }}

@endif
{{ trans('emails.email_success_thanks', ['app_name' => config('app.name')]) }}

@endcomponent
