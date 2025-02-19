@php
    $input_name = $name;
    $remove_input_name = 'remove_file_' . $name;
    if ($locale) {
        $input_name = $locale . '[' . $name . ']';
        $remove_input_name = $locale . '[' . 'remove_file_' . $name . ']';
    }
@endphp
<div class="form-input-container">
    @include('darpersocms::cms.components/form-fields/label')
    @if (isset($value) && $value)
        <div class="img-container position-relative single-image pb-2">
            <img src="{{ Storage::url($value) }}" class="img-thumbnail">
            <div class="bg-danger text-white delete-btn remove-current-image">
                <i class="fa fa-times" aria-hidden="true"></i>
                <input name="{{ $remove_input_name }}" value="">
            </div>
        </div>
    @endif
    <label class="custom-file-wrapper placeholder w-100" data-placeholder="Upload image" data-text="Upload image">
        <input type="file" class="custom-form-input" name="{{ $input_name }}">
    </label>
</div>
