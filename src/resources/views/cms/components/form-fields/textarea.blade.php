@php
	$input_name = $name;
	if ($locale) {
		$input_name = $locale . '[' . $name . ']';
	}
@endphp
<div class="form-input-container">
	@include('darpersocms::cms.components/form-fields/label')
    <div class="">
	    <textarea class="custom-form-input" name="{{ $input_name }}" rows="5" 
		{{-- onkeyup="wordCount(this)" --}}
		
		
		>{{ $value }}</textarea>
    </div>
</div>
