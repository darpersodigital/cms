@php
	$input_name = $name;
	if ($locale) {
		$input_name = $locale . '[' . $name . ']';
	}
@endphp
<div class="form-input-container {{isset($style) ? $style :""}}">
	@include('darpersocms::cms.components/form-fields/label')
	<input class="custom-form-input date-picker" name="{{ $input_name }}" value="{{ $value }}">
	  @include('darpersocms::cms.components.form-fields.field-error')
</div>