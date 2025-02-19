@php
    $input_name = $name;
    if ($locale) {
        $input_name = $locale . '[' . $name . ']';
    }
    $currentValue = $value;
    if (!isset($value) || !$value) {
        $value = [];
    } else {
        $value = json_decode($value);
    }
@endphp

<div class="form-input-container multiple-images-container">
    @include('darpersocms::cms.components/form-fields/label')
    <label class="custom-file-wrapper placeholder" data-text="Upload images">
        <input type="file" class="custom-form-input" multiple name="{{ $input_name }}[]" value="">
    </label>

    <input type="hidden" class="custom-form-input current-multiple-images-value" name="current_{{ $input_name }}[]"
        value="{{ $currentValue }}">
    <div class="images-preview px-3 py-3" {!! count($value) ? '' : 'style="display: none;"' !!}>
        <div class="row images-sortable">
            @foreach ($value as $image)
            @if (getType($image)=='string') 
                <div class="col-auto single-multiple-image" data-image="{!! $image !!}">
                    <img class="img-thumbnail" src="{{ Storage::url($image) }}">
                    <div class="bg-danger text-white delete-btn" data-image="{{ $image }}">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </div>
                    <input type="hidden" name="{{ $input_name }}[]" value="{{ $image }}">
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
