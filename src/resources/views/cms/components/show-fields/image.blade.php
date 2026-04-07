<div class="py-2">
	<label class="mb-3"><b>{{ $label }}</b></label>
	<div class="">
		@if ($value)
			<img class="img-thumbnail" src="{{ Storage::url($value) }}" data-testID="image-{{$testID ?? ""}}">
		@else
			<p class="m-0" data-testID="no-image-{{$testID}}">No image</p>
		@endif
	</div>
</div>