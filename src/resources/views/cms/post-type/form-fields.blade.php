@php
    $value = isset($row)
        ? ($locale
            ? ($row->translate($locale)
                ? $row->translate($locale)[$field['name']]
                : null)
            : $row[$field['name']])
        : null;

    $label = ucwords(str_replace(['_id', '_'], ['', ' '], $field['name']));
    $isRequired = $field['nullable'] ? false : true;
    $description = $field['description'] ?? '';

    if ($field['form_field'] == 'password' || $field['form_field'] == 'password with confirmation') {
        $value = '';
    }

    if ($field['form_field'] != 'multiple images' && $field['form_field'] != 'multiple files' && !$value) {
        if ($locale) {
            $value = old($locale . '.' . $field['name']);
        } else {
            $value = old($field['name']);
        }
    }

    $fieldAttributes = [
        'label' => $label,
        'type' => 'text',
        'name' => $field['name'],
        'testID' => $field['name'],
        'value' => $value,
        'required' => $isRequired,
        'description' => $description,
        'locale' => $locale,
    ];

    // Set dynamic path based on form field type
    $fieldComponent = match ($field['form_field']) {
        'textarea',
        'rich-textbox',
        'file',
        'video',
        'image',
        'multiple images',
        'multiple files',
        'multiple videos',
        'date',
        'time',
        'checkbox',
        'password with confirmation'
            => 'cms.components/form-fields/' . str_replace(' ', '-', $field['form_field']),
        default => 'cms.components/form-fields/TextInput',
    };

    // Special handling for select fields
    if (in_array($field['form_field'], ['select', 'select multiple'])) {
        $fieldAttributes['options'] = $extra_variables[$field['form_field_configs_1']];
        $fieldAttributes['store_column'] = 'id';
        $fieldAttributes['display_column'] = $field['form_field_configs_2'];

        if ($field['form_field'] === 'select multiple') {
            $fieldAttributes['value'] = isset($row)
                ? ($locale
                    ? $row->translate($locale)[str_replace('_id', '', $field['name'])]->pluck('id')->toArray()
                    : $row[str_replace('_id', '', $field['name'])]->pluck('id')->toArray())
                : '';
        }
        $fieldComponent = 'cms/components/form-fields/' . str_replace(' ', '-', $field['form_field']);
    }

    // Special handling for slug field
    if (
        $field['form_field'] === 'slug' &&
        (!isset($row) || isset($locale) || (isset($row) && $field['form_field_configs_1']))
    ) {
        $fieldAttributes['slug_origin'] = $field['form_field_configs_1'];
        $fieldAttributes['is_slug'] = true;
        $fieldAttributes['readonly'] = $field['form_field_configs_2'] == '0';
        $fieldComponent = 'cms/components/form-fields/slug';
    }

    // Special handling for password and color picker
    if (in_array($field['form_field'], ['password', 'color picker', 'number'])) {
        if ($field['form_field'] == 'password') {
            $fieldAttributes['type'] = 'password';
        } elseif ($field['form_field'] == 'color picker') {
            $fieldAttributes['type'] = 'color';
        } elseif ($field['form_field'] == 'number') {
            $fieldAttributes['type'] = 'number';
        }
    }

    if ($field['form_field'] === 'time') {
        $fieldAttributes['format24'] = $field['form_field_configs_2'] === '1';
    }

    // Special handling for checkbox
    if ($field['form_field'] === 'checkbox') {
        $fieldAttributes['checked'] = $value;
        unset($fieldAttributes['value']);
    }

    if (isset($row) && !$field['can_update']) {
        $fieldComponent = null;
        $fieldAttributes = null;
    }

    if (!isset($row) && !$field['can_create']) {
        $fieldComponent = null;
        $fieldAttributes = null;
    }
@endphp
@if (isset($fieldComponent) && isset($fieldAttributes))
    <div class="mb-3">
        @include('darpersocms::' . $fieldComponent, $fieldAttributes)
    </div>
@endif
