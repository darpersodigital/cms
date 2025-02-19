<div class="form-input-container">
	@include('darpersocms::cms.components/form-fields/label')
	<select class="custom-form-input custom-select w-100" name="{{ $name }}">
		<option></option>
		@foreach($options as $option)
			<option value="{{ $option[$store_column] }}" {{ $value == $option[$store_column] ? 'selected' : '' }}>{{ strip_tags($option[$display_column]) }}</option>
		@endforeach
	</select>
</div>