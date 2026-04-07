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
        <div class="file-input-container position-relative pr-3 pb-2">
            <div class="position-relative pr-3 pt-2 d-inline">
                <a href="{{ Storage::url($value) }}" target="_blank" class="theme-btn sm"><i class="fa fa-file"
                        aria-hidden="true"></i><span class="btn-sm">View Video</span></a>
                <div class="bg-danger text-white delete-btn  remove-current-file"
                    data-testid="remove-current-video-{{ $locale ? $locale . '-' : '' }}{{ $testID ?? '' }}">
                    <i class="fa fa-times" aria-hidden="true"></i>
                    <input name="{{ $remove_input_name }}" value="">
                </div>
            </div>
        </div>
    @endif
    <label class="custom-file-wrapper placeholder w-100" data-placeholder="Upload file" data-text="Upload Video">
        <input type="file" class="custom-form-input" name="{{ $input_name }}"
            data-testid="video-input-input-{{ $locale ? $locale . '-' : '' }}{{ $testID ?? '' }}">
    </label>

    @include('darpersocms::cms.components.form-fields.field-error')

</div>
