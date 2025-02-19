<div class="py-2">
	<label class="mb-3"><b>{{ $label }}</b></label>
	<div class="">
		@if ($value)
			<img class="img-thumbnail" src="{{ Storage::url($value) }}">
		@else
			<p class="m-0">No image</p>
		@endif
	</div>
</div>