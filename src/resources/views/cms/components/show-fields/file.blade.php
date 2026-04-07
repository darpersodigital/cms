<div class="py-2">
    <label class="mb-3"><b>{{ $label }}</b></label>
    <div class="">
        @if ($value)
            <a href="{{ Storage::url($value) }}" data-testID="file-{{ $testID ?? '' }}" target="_blank"><i
                    class="fa fa-file" aria-hidden="true"></i><span class="btn-sm">view file</span></a>
        @else
            <p class="m-0" data-testID="no-file-{{$testID}}">No file</p>
        @endif
    </div>
</div>
