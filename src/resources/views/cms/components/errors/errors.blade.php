@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <p class="m-0">{{ $error }}</p>
        @endforeach
    </div>
@endif
