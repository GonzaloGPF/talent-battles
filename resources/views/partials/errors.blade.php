@if (isset($errors) && $errors->any())
    <div class="w-full text-center mt-2">
        @foreach ($errors->all() as $error)
            <span class="rounded-lg bg-red-300 px-3 inline-block mx-auto">
                    {{ $error }}
                </span>
        @endforeach
    </div>
@endif
