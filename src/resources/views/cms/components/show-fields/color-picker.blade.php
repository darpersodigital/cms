<div class="py-2">
	<label><b>{{ $label }}</b></label>
	<div class="">
        <div class="d-flex align-items-center">
            <div class="rounded mr-1" style="width: 22px; height: 22px; background-color: {{ $row[$field['name']] }}"></div>
            <span>{{ $row[$field['name']] }}</span>
        </div>
	</div>
</div>
