<div class="py-2">
	<label class="mb-3"><b>{{ $label }}</b></label>
	@php
		$value = json_decode($value);
	@endphp
	<div class="">
		@if (isset($value->file))
			<img class="img-thumbnail" src="{{ Storage::url($value->file) }}" data-testID="image-{{$testID ?? ""}}" 
			alt="{{$value->alt}}"
			>
		@else
			<p class="m-0" data-testID="no-image-{{$testID}}">No image</p>
		@endif
	</div>
</div>