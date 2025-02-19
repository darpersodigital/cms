@php
$input_name = $name;
if (isset($locale) && $locale!=null) {
    $input_name = $locale . '[' . $name . ']';
}
@endphp

<div class="form-input-container">
    @include('darpersocms::cms.components/form-fields/label')
    <div class="">
        <input class="custom-form-input" name="{{ $input_name }}" type="{{ $type }}" value="{{ $value }}" {!! isset($slug_origin) ? 'data-slug-origin="' . $slug_origin . '"' : '' !!}
        
         >

    </div>
</div>
