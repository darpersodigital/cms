@php
$input_name = $name;
$slug_origin_value = $slug_origin;
if (isset($locale) && $locale!=null) {
    $input_name = $locale . '[' . $name . ']';
}

if (isset($locale) && $locale!=null) {
    $slug_origin_value = $locale . '[' . $slug_origin_value . ']';
}
@endphp

<div class="form-input-container form-input-container input-wrapper mt-3">
    @include('darpersocms::cms.components/form-fields/label')
    <div class="">
        <input class="custom-form-input slugify" name="{{ $input_name }}" type="{{ $type }}" value="{{ $value }}"
        data-slug-origin="{{$slug_origin_value}}"
        {!! isset($readonly) && $readonly ? "readonly":"" !!}
         >

    </div>

    @include('darpersocms::cms.components.form-fields.field-error')

</div>
