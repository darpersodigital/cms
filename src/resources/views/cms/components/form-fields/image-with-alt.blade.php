@php
    $alt_name = $name . '_alt';
    if ($locale) {
        $alt_name = $name . '_alt';
    }
    $decoded_value = json_decode($value);
    $value = isset($decoded_value->file) ? $decoded_value->file : null;
    $value_alt = isset($decoded_value->alt) ? $decoded_value->alt : null;
@endphp

<div class="file-with-alt">
    @include('darpersocms::cms.components/form-fields/image')
    <div class="alt-input">
        @include('darpersocms::cms.components/form-fields/TextInput', [
            'label' => null,
            'styles' => 'mt-0',
            'maxlength' => '150',
            'value' => $value_alt,
            'name' => $alt_name,
            'placeholder' => 'SEO image alt',
        ])
    </div>
</div>
