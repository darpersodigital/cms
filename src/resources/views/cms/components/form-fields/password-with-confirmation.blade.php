@php
    $input_name = $name;
    $input_confirmation_name = $name . '_confirmation';
    if ($locale) {
        $input_name = $locale . '[' . $name . ']';
        $input_confirmation_name = $locale . '[' . $name . '_confirmation]';
    }
@endphp
<div class="form-input-container">
    @include('darpersocms::cms.components/form-fields/label')
    <input class="custom-form-input" name="{{ $input_name }}" type="password">
</div>
<div class="form-input-container mt-2">
    @include('darpersocms::cms.components/form-fields/label', ['label' => 'Confirm ' . $label])
    <input class="custom-form-input" name="{{ $input_confirmation_name }}" type="password">
</div>
