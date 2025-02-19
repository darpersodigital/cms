@php
	$input_name = $name;
	if ($locale) {
		$input_name = $locale . '[' . $name . ']';
	}
@endphp
<div class="form-input-container">
	@include('darpersocms::cms.components/form-fields/label')
    <div class="">
        <textarea name="{{ $input_name }}" id="ckeditor_{{ $input_name }}" >{{ $value }}</textarea>
    </div>
</div>