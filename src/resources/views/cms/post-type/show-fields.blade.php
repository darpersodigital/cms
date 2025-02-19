@php
    $default_value =
        isset($locale) && isset($row->translate($locale)[$field['name']])
            ? $row->translate($locale)[$field['name']]
            : $row[$field['name']];

    $label = ucwords(str_replace(['_id', '_'], ['', ' '], $field['name']));

    $fieldAttributes = [
        'label' => $label,
        'value' => $default_value,
        'name' => $field['name'],
    ];

    // Set dynamic path based on form field type
    $fieldComponent = match ($field['form_field']) {
        'image' => 'cms/components/show-fields/image',
        'multiple images', 'multiple-images' => 'cms/components/show-fields/images',
        'file' => 'cms/components/show-fields/file',
        'checkbox' => 'cms/components/show-fields/boolean',
        'color picker' => 'cms/components/show-fields/color-picker',
        default => 'cms/components/show-fields/text',
    };

    // Special handling for select fields
    if ($field['form_field'] === 'select' && isset($row[str_replace('_id', '', $field['name'])])) {
        $fieldAttributes['value'] = $locale
            ? $row->translate($locale)[$field['name']]
            : $row[str_replace('_id', '', $field['name'])][$field['form_field_configs_2']];
        $fieldComponent = 'cms/components/show-fields/text';
    }

    if ($field['form_field'] === 'select multiple' && isset($row[str_replace('_id', '', $field['name'])])) {
        $v = '';
        foreach ($row[str_replace('_id', '', $field['name'])] as $i => $pivot) {
            $v .= $i ? ', ' : '';
            $v .= $pivot[$field['form_field_configs_2']] ;
        }
        $fieldAttributes['value'] = $v;
        $fieldComponent = 'cms/components/show-fields/text';
    }

    // Skip rendering for password fields or unreadable fields
    $skipFields = ['password', 'password with confirmation'];
@endphp

@if (!in_array($field['form_field'], $skipFields) && $field['can_read'] != 0)
    @include('darpersocms::'.$fieldComponent, $fieldAttributes)
@endif