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
		@if ($images)
			@foreach($images as $image)
				<img class="img-thumbnail" src="{{ Storage::url($image) }}" style="height: 100px;">
			@endforeach
		@else
			<p class="m-0">No image</p>
		@endif
	</div>
</div>