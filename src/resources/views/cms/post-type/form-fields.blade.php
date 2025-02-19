@php
    $value = isset($row) 
        ? ($locale && $row->translate($locale) ? $row->translate($locale)[$field['name']] : $row[$field['name']]) 
        : '';

    $label = ucwords(str_replace(['_id', '_'], ['', ' '], $field['name']));
    $isRequired = $field['nullable'] ? false : true;
    $description = $field['description'] ?? '';

	if ($field['form_field'] == 'password' || $field['form_field'] == 'password with confirmation') $value='';

    $fieldAttributes = [
        'label' => $label,
		'type'=>'text',
        'name' => $field['name'],
        'value' => $value,
        'required' => $isRequired,
        'description' => $description,
        'locale' => $locale,
    ];

    // Set dynamic path based on form field type
    $fieldComponent = match ($field['form_field']) {
        'textarea', 'rich-textbox', 'file', 'image', 'multiple images', 'date', 'time', 'checkbox', 'password with confirmation' => 'cms.components/form-fields/' . str_replace(' ', '-', $field['form_field']),
        default => 'cms.components/form-fields/input'
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
    if ($field['form_field'] === 'slug' && (!isset($row) || (isset($row) && $field['form_field_configs_2']))) {
        $fieldAttributes['slug_origin'] = $field['form_field_configs_1'];
        $fieldComponent = 'cms/components/form-fields/input';
    }

    // Special handling for password and color picker
    if (in_array($field['form_field'], ['password', 'color picker','number'])) {
        if( $field['form_field'] =='password')  $fieldAttributes['type'] = 'password';
        else if ($field['form_field']=='color picker')   $fieldAttributes['type'] ='color';
        else if  ($field['form_field']=='number') $fieldAttributes['type'] ='number';
    }

    // Special handling for checkbox
    if ($field['form_field'] === 'checkbox') {
        $fieldAttributes['checked'] = $value;
        unset($fieldAttributes['value']); // Checkbox uses "checked" instead of "value"
    }
@endphp

<div class="mb-3">
    @include('darpersocms::'.$fieldComponent, $fieldAttributes)
</div>