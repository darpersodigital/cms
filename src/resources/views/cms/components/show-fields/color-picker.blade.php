<div class="py-2">
	<label><b>{{ $label }}</b></label>
	<div class="">
        <div class="d-flex align-items-center">
            <div class="rounded mr-1" style="width: 22px; height: 22px; background-color: {{ $value }}"></div>
            <span data-testID="color-picker-{{$testID ?? ""}}" >{{ $value }}</span>
        </div>
	</div>
</div>
