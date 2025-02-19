@php
	$input_name = $name;
	if (isset($locale)) {
		$input_name = $locale . '[' . $name . ']';
	}
@endphp
<div class="form-input-container">
	@if (!isset($inline_label))
        @include('darpersocms::cms.components/form-fields/label')
	@endif
	<label class="checkbox-container">
		<input type="checkbox" class="custom-form-input" name="{{ $input_name }}" {!! $checked ? 'checked=""' : '' !!}>
		<div></div>
		@if (isset($inline_label))
			<span class="d-inline-block align-middle mb-0 ml-1">{{ $label }}</span>
		@endif
	</label>
</div>