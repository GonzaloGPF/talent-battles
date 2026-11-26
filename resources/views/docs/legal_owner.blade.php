<ul>
    @foreach ($rows as $label => $value)
        <li><strong>{{ $label }}:</strong> {{ $value }}</li>
    @endforeach
</ul>
