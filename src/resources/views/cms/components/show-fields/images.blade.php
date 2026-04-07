@php

if ($value) {
	$images = json_decode($value);
	if (!$images) $images = [];
} else {
	$images = [];
}
@endphp
<div class="py-2">
	<label class="mb-3"><b>{{ $label }}</b></label>
	<div class="">
		@if ($images && count($images)>0)
			@foreach($images as $image)
				<img class="img-thumbnail mb-2" src="{{ Storage::url($image) }}" style="height: 100px;" data-testID="images-{{$testID ?? ""}}">
			@endforeach
		@else
			<p class="m-0" data-testID="no-images-{{$testID ?? ""}}">No image</p>
		@endif
	</div>
</div>