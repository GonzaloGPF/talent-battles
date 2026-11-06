@component('mail::message')
@if(isset($userName))
# {{ trans('emails.hello_name', ['name' =>  $userName ]) }}
@endif

@component('mail::table')
@foreach($lines as $line)
| {{ ta('description') }}    | {{ ta('amount') }}           | {{ ta('commission') }} ({{ $commission }}%)| {{ ta('total') }}           |
| -------------------------- | ---------------------------- | ------------------------------------------ | ---------------------------:|
| {{ $line['description'] }} | {{ money($line['amount']) }} | -{{ money($line['commission_amount']) }}   | {{ money($line['total']) }} |
@endforeach
| {{ tl('final') }}:         |                              |                                            | {{ money($total) }}         |
@endcomponent

{{ trans('emails.sale_congrats') }}

{{ trans('emails.email_success_thanks', ['app_name' => config('app.name')]) }}.

@endcomponent
