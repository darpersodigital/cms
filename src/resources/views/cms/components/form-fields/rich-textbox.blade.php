@php
	$input_name = $name;
	if ($locale) {
		$input_name = $locale . '[' . $name . ']';
	}
@endphp
<div class="form-input-container" >
	@include('darpersocms::cms.components/form-fields/label')
    <div class="" data-testid="rich-textbox-{{$input_name}}">
        <textarea name="{{ $input_name }}" id="ckeditor_{{ $input_name }}" >{{ $value }}</textarea>
    </div>
	@include('darpersocms::cms.components.form-fields.field-error')

</div>