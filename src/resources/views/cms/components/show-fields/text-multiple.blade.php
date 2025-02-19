<div class=py-2">
	<label><b>{{ $label }}</b></label>
	<div class="">
		@if(isset($texts))
			@foreach($texts as $text)
				<p class="">{{ strip_tags($text[$display_column]) }}</p>
			@endforeach
		@endif
	</div>
</div>