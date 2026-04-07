<div class="py-2">
    <label class="mb-3"><b>{{ $label }}</b></label>
    <div class="">
        @if ($value)
            <a href="{{ Storage::url($value) }}" data-testID="video-{{ $testID ?? '' }}" target="_blank"><i
                    class="fa-solid fa-video" aria-hidden="true"></i><span class="btn-sm">View Video</span></a>
        @else
            <p class="m-0" data-testID="no-ideo-{{$testID}}">No Video</p>
        @endif
    </div>
</div>
