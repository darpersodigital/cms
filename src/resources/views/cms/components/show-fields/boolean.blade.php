<div class="py-2">
	<label ><b>{{ $label }}</b></label>
	<div class="">
		@if ($value)
			<i class="fa fa-check" aria-hidden="true"></i>
		@else
			<i class="fa fa-times" aria-hidden="true"></i>
		@endif
	</div>
</div>