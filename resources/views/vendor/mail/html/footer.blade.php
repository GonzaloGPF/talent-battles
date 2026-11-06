<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center" style="font-size: small">
<p>
{{ trans('emails.footer_disclaimer') }}
</p>
<a href="{{ url("/?open_contact=true") }}">{{ trans('emails.contact_with_us') }}</a>
</td>
</tr>
<tr>
<td class="content-cell" align="center">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
