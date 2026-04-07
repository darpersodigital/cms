<div class="py-2">
	<label ><b>{{ $label }}</b></label>
	<div class="">
		@if ($value)
			<i class="fa fa-check"  data-testID="boolean-checked-{{$testID ?? ""}}" aria-hidden="true"></i>
		@else
			<i class="fa fa-times" data-testID="boolean-unchecked-{{$testID ?? ""}}" aria-hidden="true"></i>
		@endif
	</div>
</div>