@php
    $input_name = $name;
    $input_confirmation_name = $name . '_confirmation';
    if ($locale) {
        $input_name = $locale . '[' . $name . ']';
        $input_confirmation_name = $locale . '[' . $name . '_confirmation]';
    }
@endphp


@include('darpersocms::cms.components.form-fields.TextInput', [
    'label' => $label,
    'name' => $input_name,
    'testID' => $input_name,
    'type' => 'password',
    'value' => "",
    'locale' => null,
    'locale' => null,
    'error' => $errors->first($input_name),
    'required' => isset($required) ? $required : true,
])


@include('darpersocms::cms.components.form-fields.TextInput', [
    'label' => "Confirm " .$label,
    'name' => $input_confirmation_name,
    'type' => 'password',
    'value' => "",
    'locale' => null,
    'testID' => $input_confirmation_name,
    'error' => $errors->first($input_confirmation_name),
    'required' => isset($required) ? $required : true,
])
