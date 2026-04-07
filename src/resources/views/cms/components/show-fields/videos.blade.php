@php

if ($value) {
	$files = json_decode($value);
	if (!$files) $files = [];
} else {
	$files = [];
}
@endphp

<div class="py-2">
	<label class="mb-3"><b>{{ $label }}</b></label>
	<div class="">
		@if ($files && count($files)>0)
			@foreach($files as $file)
				<a href="{{ Storage::url($file) }}" 
				data-testID="videos-{{$testID ?? ""}}"
				target="_blank" class="mr-2 mb-2"><i class="fa fa-file" aria-hidden="true"></i><span class="btn-sm">View Video</span></a>
			@endforeach
		@else
			<p class="m-0" data-testID="no-videos-{{$testID ?? ""}}">No Videos</p>
		@endif
	</div>
</div>