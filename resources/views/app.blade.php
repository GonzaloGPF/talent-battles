@extends('layouts.base')
@push('scripts')
    <script>
        window.flash_message = {!! json_encode(session()->pull('message')) !!};
        window.invitation = JSON.parse('{!! json_encode($invitation ?? null) !!}');
    </script>
@endpush

@section('content')
    <div id="app">
        <app-root v-cloak></app-root>

        <div v-if="!$data"
             class="animate-animated animate-fadeIn loader-container">
            @include('partials.loader')
        </div>
    </div>
@endsection
