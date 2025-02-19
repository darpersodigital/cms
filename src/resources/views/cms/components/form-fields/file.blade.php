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
        <div class="file-input-container ">
            <div class=" d-flex align-items-center pb-2">
                <a href="{{ Storage::url($value) }}" target="_blank"><i class="fa fa-file" aria-hidden="true"></i><span
                        class="btn-sm">view file</span></a>

                <div class="bg-danger text-white delete-btn  remove-current-file">
                    <i class="fa fa-times" aria-hidden="true"></i>
                    <input name="{{ $remove_input_name }}" value="">
                </div>
            </div>
        </div>
    @endif
    <label class="custom-file-wrapper placeholder w-100" data-placeholder="Upload file" data-text="Upload file">
        <input type="file" class="custom-form-input" name="{{ $input_name }}">
    </label>
</div>
